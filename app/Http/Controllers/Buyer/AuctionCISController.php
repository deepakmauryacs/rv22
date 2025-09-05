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
        // check auction
        $auctionExists = DB::table('rfq_auctions')->where('rfq_no', $rfq_id)->exists();
        if (!$auctionExists) {
            return response("<script>
                alert('THIS RFQ NOT BELONG TO LIVE AUCTIONS');
                window.location.href='" . route('buyer.dashboard') . "';
            </script>");
        }

        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }
        if ($rfq_data->buyer_rfq_status == 1) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS did not received any vendor quote to open.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if (!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)) {
            return back()->with('error', 'No RFQ found');
        }

        $uom = getUOMList();

        $nature_of_business = DB::table("nature_of_business")
            ->select("id", "business_name")
            ->orderBy("id", "DESC")
            ->pluck("business_name", "id")->toArray();

        // $cis = Rfq::rfqDetails($rfq_id);
        $cis = Rfq::rfqAuctionDetails($rfq_id);

        $rfq = $cis['rfq'];

        $filter['sort_price'] = request('sort_price');
        $filter['location'] = request('location');
        $filter['state_location'] = request('state_location');
        $filter['country_location'] = request('country_location');
        $filter['last_vendor'] = request('last_vendor');
        $filter['favourite_vendor'] = request('favourite_vendor');
        $filter['from_date'] = request('from_date');
        $filter['to_date'] = request('to_date');

        $is_date_filter = !empty($filter['from_date']) || !empty($filter['to_date']);

        $currencies = DB::table('currencies')->where('status', '1')->get();

        // ==== Prefill (Edit) data from new tables ====
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();

        $editId = $auction->id ?? null;

        // Selected vendors (prefer rfq_vendor_auctions; fallback to forward_auction_vendors)
        $selectedVendorIds = [];
        if ($editId) {
            $selectedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn ($v) => (int) $v)
                ->toArray();

            if (empty($selectedVendorIds)) {
                $selectedVendorIds = DB::table('forward_auction_vendors')
                    ->where('auction_id', $editId)
                    ->pluck('vendor_id')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();
            }
        }

        // Variant start prices keyed by rfq_variant_id
        $prefillVariantPrices = [];
        if ($editId) {
            $prefillVariantPrices = DB::table('rfq_auction_variants')
                ->where('auction_id', $editId)
                ->pluck('start_price', 'rfq_variant_id') // [rfq_variant_id => start_price]
                ->toArray();
        }

        // Header fields (date/time/currency/decrement) for inputs
        $prefill = [];
        if ($auction) {
            // rfq_auctions.auction_date is stored as YYYY-MM-DD (varchar)
            try {
                $prefill['auction_date']  = Carbon::createFromFormat('Y-m-d', $auction->auction_date)->format('d/m/Y');
            } catch (\Throwable $e) {
                $prefill['auction_date']  = $auction->auction_date; // fallback
            }
            $prefill['auction_time']      = $auction->auction_start_time;  // HH:mm:ss
            $prefill['min_bid_currency']  = $auction->currency ?? 'INR';
            $prefill['min_bid_decrement'] = (float) $auction->min_bid_decrement;
            $prefill['auction_type']          = $auction->auction_type;
        }

        /**
         * ==== LIVE AUCTION STATUS (CI -> Laravel) ====
         * Mirrors the CI code logic using Carbon and DB facade.
         * Determines current_status: 1=Active, 2=Scheduled, 3=Closed
         */
        // Derive RFQ number safely from $rfq (could be array or object) or fall back to $rfq_id
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
            // Use Asia/Kolkata to match your environment
            $today_date    = Carbon::today('Asia/Kolkata')->toDateString();   // 'Y-m-d'
            $current_time  = Carbon::now('Asia/Kolkata')->format('H:i:s');    // 'H:i:s'

            $auction_date       = $liveAuction->auction_date;                 // expect 'Y-m-d'
            $auction_start_time = $liveAuction->auction_start_time;           // 'H:i:s'
            $auction_end_time   = $liveAuction->auction_end_time;             // 'H:i:s'

            if ($auction_date == $today_date) {
                if ($current_time >= $auction_start_time && $current_time <= $auction_end_time) {
                    $current_status = 1; // Active
                } elseif ($current_time < $auction_start_time) {
                    $current_status = 2; // Scheduled
                } else {
                    $current_status = 3; // Closed
                }
            } elseif ($auction_date < $today_date) {
                $current_status = 3; // Closed
            } else {
                $current_status = 2; // Scheduled
            }
        }

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
