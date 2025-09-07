<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use Carbon\Carbon;
use DB;

class AuctionCISController extends Controller
{
    public function index($rfq_id)
    {
        // 1) Ensure RFQ is in live auctions
        $auctionExists = DB::table('rfq_auctions')->where('rfq_no', $rfq_id)->exists();
        if (!$auctionExists) {
            return response("<script>
                alert('THIS RFQ NOT BELONG TO LIVE AUCTIONS');
                window.location.href='" . route('buyer.dashboard') . "';
            </script>");
        }

        // 2) Buyer + RFQ ownership checks
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $parent_user_id)
            ->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }
        if ((int)$rfq_data->buyer_rfq_status === 1) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS did not received any vendor quote to open.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if (!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)) {
            return back()->with('error', 'No RFQ found');
        }

        // 3) Static lookups
        $uom = getUOMList();
        $nature_of_business = DB::table('nature_of_business')
            ->select('id', 'business_name')
            ->orderByDesc('id')
            ->pluck('business_name', 'id')
            ->toArray();

        // 4) Current auction row (latest by id) — used for filtering vendors + prefill
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();
        $editId = $auction->id ?? null;

        // 5) CIS payload from model
        $cis = Rfq::rfqAuctionDetails($rfq_id);
        $rfq = $cis['rfq'] ?? null;

        // 6) STRICT: Vendors must be only those present in rfq_vendor_auctions for this auction
        $allowedVendorIds = [];
        if ($editId) {
            $allowedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn ($v) => (int)$v)
                ->toArray();
        }
        // Filter cis['vendors'] by allowed list (handles array-key or vendor_user_id inside row)
        $cis['vendors'] = collect($cis['vendors'] ?? [])
            ->filter(function ($row, $key) use ($allowedVendorIds) {
                $id = $key;
                if (is_array($row)) {
                    $id = $row['vendor_user_id'] ?? $key;
                } elseif (is_object($row)) {
                    $id = $row->vendor_user_id ?? $key;
                }
                return in_array((int)$id, $allowedVendorIds, true);
            })
            ->toArray();

        // 7) Request filters
        $filter = [
            'sort_price'       => request('sort_price'),
            'location'         => request('location'),
            'state_location'   => request('state_location'),
            'country_location' => request('country_location'),
            'last_vendor'      => request('last_vendor'),
            'favourite_vendor' => request('favourite_vendor'),
            'from_date'        => request('from_date'),
            'to_date'          => request('to_date'),
        ];
        $is_date_filter = !empty($filter['from_date']) || !empty($filter['to_date']);
        $currencies = DB::table('currencies')->where('status', '1')->get();

        // 8) Selected vendor ids (STRICT: only from rfq_vendor_auctions)
        $selectedVendorIds = [];
        if ($editId) {
            $selectedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn ($v) => (int)$v)
                ->toArray();
        }

        // 9) Variant start prices keyed by rfq_variant_id (for header/table prefill)
        $prefillVariantPrices = [];
        if ($editId) {
            $prefillVariantPrices = DB::table('rfq_auction_variants')
                ->where('auction_id', $editId)
                ->pluck('start_price', 'rfq_variant_id')
                ->toArray();
        }

        // 10) Header prefill (date/time/currency/decrement/type)
        $prefill = [];
        if ($auction) {
            try {
                // auction_date might be 'Y-m-d' or 'd/m/Y' etc.; display as d/m/Y
                $prefill['auction_date'] = Carbon::parse($auction->auction_date, 'Asia/Kolkata')->format('d/m/Y');
            } catch (\Throwable $e) {
                $prefill['auction_date'] = $auction->auction_date; // fallback to raw
            }
            $prefill['auction_time']      = $auction->auction_start_time;
            $prefill['min_bid_currency']  = $auction->currency ?? 'INR';
            $prefill['min_bid_decrement'] = (float)$auction->min_bid_decrement;
            $prefill['auction_type']      = $auction->auction_type;
        }

        // 11) LIVE AUCTION STATUS (robust parsing)
        $rfqNo = $rfq_id;
        if (is_array($rfq)) {
            $rfqNo = $rfq['rfq_id'] ?? $rfq_id;
        } elseif (is_object($rfq)) {
            $rfqNo = $rfq->rfq_id ?? $rfq_id;
        }

        $liveAuction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfqNo)
            ->orderByDesc('id')
            ->first();

        $current_status = null; // 1=Active, 2=Scheduled, 3=Closed
        if (!empty($liveAuction)) {
            $tz  = 'Asia/Kolkata';
            $now = Carbon::now($tz);

            // Flexible parsers
            $parseDate = function ($v) use ($tz) {
                if (!$v) return null;
                $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];
                foreach ($formats as $fmt) {
                    try { return Carbon::createFromFormat($fmt, trim($v), $tz)->startOfDay(); } catch (\Throwable $e) {}
                }
                try { return Carbon::parse($v, $tz)->startOfDay(); } catch (\Throwable $e) { return null; }
            };
            $parseTime = function ($v) use ($tz) {
                if (!$v) return null;
                $formats = ['H:i:s', 'H:i', 'h:i A', 'g:i A', 'h:iA', 'g:iA'];
                foreach ($formats as $fmt) {
                    try { return Carbon::createFromFormat($fmt, trim(strtoupper($v)), $tz); } catch (\Throwable $e) {}
                }
                try { return Carbon::parse($v, $tz); } catch (\Throwable $e) { return null; }
            };

            $d  = $parseDate($liveAuction->auction_date ?? null);
            $t1 = $parseTime($liveAuction->auction_start_time ?? null);
            $t2 = $parseTime($liveAuction->auction_end_time ?? null);

            if ($d && $t1 && $t2) {
                $start = $d->copy()->setTime($t1->hour, $t1->minute, $t1->second);
                $end   = $d->copy()->setTime($t2->hour, $t2->minute, $t2->second);
                // Cross-midnight window (e.g., 10 PM to 2 AM)
                if ($end->lessThanOrEqualTo($start)) {
                    $end->addDay();
                }

                if ($now->betweenIncluded($start, $end)) {
                    $current_status = 1; // Active
                } elseif ($now->lt($start)) {
                    $current_status = 2; // Scheduled
                } else {
                    $current_status = 3; // Closed
                }
            } else {
                // Conservative fallback
                $current_status = 2; // Scheduled
            }
        }

        // 12) Render
        return view('buyer.auction.cis.rfq-cis', compact(
            'uom',
            'cis',
            'rfq',
            'nature_of_business',
            'filter',
            'is_date_filter',
            'currencies',
            'auction',
            'editId',
            'selectedVendorIds',
            'prefill',
            'prefillVariantPrices',
            'liveAuction',
            'current_status',
        ));
    }
}
