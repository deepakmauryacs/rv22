<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LiveAuctionRfqSinglePriceController extends Controller
{
    /**
     * Build ranks (L1, L2...) per RFQ variant among participating vendors.
     */
    public function getRank(Request $request)
    {
        $rfqId = $request->input('rfq_id');

        $auction = $this->latestAuctionRow($rfqId);
        $isForcestop = $auction ? ($auction->is_forcestop ?? '2') : '2';
        $auctionId   = $auction?->id;

        if (!$auctionId) {
            return response()->json([
                'is_forcestop' => '2',
                'rank'         => [],
                'lowest_price' => [],
                'message'      => 'Auction not found for this RFQ.'
            ]);
        }

        // only participating vendors for this auction
        $participatingVendorIds = DB::table('rfq_vendor_auctions')
            ->where('rfq_no', $rfqId)
            ->where('auction_id', $auctionId)
            ->pluck('vendor_id');

        if ($participatingVendorIds->isEmpty()) {
            return response()->json([
                'is_forcestop' => $isForcestop,
                'rank'         => [],
                'lowest_price' => []
            ]);
        }

        // all variants in this auction
        $variantIds = DB::table('rfq_auction_variants')
            ->where('auction_id', $auctionId)
            ->pluck('rfq_variant_id');

        if ($variantIds->isEmpty()) {
            return response()->json([
                'is_forcestop' => $isForcestop,
                'rank'         => [],
                'lowest_price' => []
            ]);
        }

        // latest price row per (variant, vendor) in this auction
        $latestPerVendor = DB::table('rfq_vendor_auction_price')
            ->selectRaw('MAX(id) as max_id, rfq_product_veriant_id, vendor_id')
            ->where('rfq_no', $rfqId)
            ->where('rfq_auction_id', $auctionId)
            ->whereIn('rfq_product_veriant_id', $variantIds)
            ->whereIn('vendor_id', $participatingVendorIds)
            ->groupBy('rfq_product_veriant_id', 'vendor_id');

        $rows = DB::table('rfq_vendor_auction_price as ap')
            ->joinSub($latestPerVendor, 't', 't.max_id', '=', 'ap.id')
            ->select([
                'ap.vendor_id',
                'ap.rfq_product_veriant_id as variant_id',
                'ap.vend_price',
            ])
            ->get()
            ->groupBy('variant_id');

        $rankArray   = [];
        $lowestPrice = [];

        foreach ($variantIds as $vid) {
            $bucket = $rows->get($vid) ?? collect();
            if ($bucket->isEmpty()) continue;

            $items = $bucket->sort(function ($a, $b) {
                if (is_null($a->vend_price) && is_null($b->vend_price)) return 0;
                if (is_null($a->vend_price)) return 1;
                if (is_null($b->vend_price)) return -1;
                return $a->vend_price <=> $b->vend_price;
            })->values();

            $rank = 1;
            foreach ($items as $idx => $item) {
                $key = $vid . $item->vendor_id;
                $rankArray[$key] = is_null($item->vend_price) ? '' : $rank;

                if (!is_null($item->vend_price)
                    && isset($items[$idx + 1])
                    && $items[$idx + 1]->vend_price != $item->vend_price) {
                    $rank++;
                }
            }

            $lowest = $items->filter(fn($r) => !is_null($r->vend_price))->min('vend_price');
            if (!is_null($lowest)) $lowestPrice[$vid] = (float) $lowest;
        }

        return response()->json([
            'is_forcestop' => $isForcestop,
            'rank'         => $rankArray,
            'lowest_price' => $lowestPrice
        ]);
    }

    /**
     * Quick check: if typed price equals current L1 (other vendor), return 'rank_1'.
     */
    public function checkRankPrice(Request $request)
    {
        $vendorId     = (int) (function_exists('getParentUserId') ? getParentUserId() : (auth()->id() ?? 0));
        $enteredPrice = round((float)$request->input('price'), 2);
        $rfqId        = $request->input('rfq_id');
        $variantId    = (int)$request->input('variant_grp_id');

        $auction = $this->latestAuctionRow($rfqId);
        if (!$auction) return response('');

        $participatingVendorIds = DB::table('rfq_vendor_auctions')
            ->where('rfq_no', $rfqId)
            ->where('auction_id', $auction->id)
            ->pluck('vendor_id');

        if ($participatingVendorIds->isEmpty()) return response('');

        $latestPerVendor = DB::table('rfq_vendor_auction_price')
            ->selectRaw('MAX(id) as max_id, vendor_id')
            ->where('rfq_no', $rfqId)
            ->where('rfq_auction_id', $auction->id)
            ->where('rfq_product_veriant_id', $variantId)
            ->whereIn('vendor_id', $participatingVendorIds)
            ->groupBy('vendor_id');

        $l1Row = DB::table('rfq_vendor_auction_price as ap')
            ->joinSub($latestPerVendor, 't', 't.max_id', '=', 'ap.id')
            ->orderBy('ap.vend_price', 'asc')
            ->select('ap.vend_price', 'ap.vendor_id')
            ->first();

        if ($l1Row) {
            $rank1Price  = round((float)$l1Row->vend_price, 2);
            $rank1Vendor = (int)$l1Row->vendor_id;

            if ($rank1Price == $enteredPrice && $rank1Vendor !== $vendorId) {
                return response('rank_1');
            }
        }

        return response('');
    }

    /**
     * Save vendor lot total + per-variant unit prices into rfq_vendor_auction_price.
     * Also upserts rfq_vendor_auction_price_total.
     *
     * Request expects (sample):
     *  - vendor_spec[14130] => "..." (optional)
     *  - total_bid_price    => "400,000.00" (Start Total shown in UI)
     *  - min_bid_decrement  => "2.00"
     *  - total_price        => "300000.00"  (your lot bid)
     *  - vendor_* fields    => saved into vend_* columns
     *  - rfq_id             => "GRBP-25-00009"
     */
    public function saveLotRfq(Request $request)
    {
        $request->validate([
            'rfq_id'          => ['required','string'],
            'total_price'     => ['required','numeric','min:0.01'],
            'total_bid_price' => ['nullable','string'],
        ]);

        $vendorId  = (int) (function_exists('getParentUserId') ? getParentUserId() : (auth()->id() ?? 0));
        $rfqId     = (string) $request->input('rfq_id');
        $lotPrice  = (float)  $request->input('total_price');
        $startTotalFromUI = (float) str_replace(',', '', (string)$request->input('total_bid_price', 0));
        $vendSpecs = (array)  $request->input('vendor_spec', []);

        // vendor_* terms
        $vend_price_basis     = $request->input('vendor_price_basis');
        $vend_payment_terms   = $request->input('vendor_payment_terms');
        $vend_delivery_period = $request->input('vendor_delivery_period');
        $vend_price_validity  = $request->input('vendor_price_validity');
        $vend_dispatch_branch = $request->input('vendor_dispatch_branch');
        $vend_currency        = $request->input('vendor_currency');

        $auction = $this->latestAuctionRow($rfqId);
        if (!$auction) {
            return response()->json(['status'=>false,'message'=>'Auction not found for this RFQ.'],404);
        }
        if ((string)($auction->is_forcestop ?? '2') === '1') {
            return response()->json(['status'=>false,'hasAuctionEnded'=>true,'message'=>'Auction has ended.']);
        }

        // Fetch auction variants (rfq_variant_id + start_price)
        $variants = DB::table('rfq_auction_variants as rav')
            ->select('rav.rfq_variant_id','rav.start_price')
            ->where('rav.auction_id', $auction->id)
            ->get();

        if ($variants->isEmpty()) {
            return response()->json(['status'=>false,'message'=>'No variants mapped to this auction.']);
        }

        // Compute Start Total (server-side sum; if missing, fallback to UI)
        $startTotal = (float) $variants->sum('start_price');
        if ($startTotal <= 0 && $startTotalFromUI > 0) {
            $startTotal = $startTotalFromUI;
        }

        if ($startTotal > 0 && $lotPrice > $startTotal) {
            return response()->json([
                'status'  => false,
                'message' => 'Your lot bid cannot exceed the Start Total (' . number_format($startTotal, 2) . ').',
            ]);
        }

        $check = $this->checkRankByPrice(
            $rfqId,
            $vendorId,
            $lotPrice,
            $startTotal,
            (float)($auction->min_bid_decrement ?? 0)
        );
        if ($check['status'] !== 1) {
            return response()->json(['status' => false, 'message' => $check['message']]);
        }

        // CI-style global adjustment %: ((StartTotal - YourTotal)/StartTotal)*100
        // Calculate the % adjustment using the original Start Total shown in the UI
        $adjustmentPercent = $this->calculate_price_adjustment($startTotalFromUI, $lotPrice);

        DB::transaction(function () use (
            $rfqId, $vendorId, $auction, $variants, $adjustmentPercent, $vendSpecs,
            $vend_price_basis, $vend_payment_terms, $vend_delivery_period,
            $vend_price_validity, $vend_dispatch_branch, $vend_currency, $lotPrice
        ) {
            // (A) Upsert lot total (if the table exists)
            if (Schema::hasTable('rfq_vendor_auction_price_total')) {
                $totalWhere = ['rfq_no'=>$rfqId,'vendor_id'=>$vendorId];
                if (Schema::hasColumn('rfq_vendor_auction_price_total','rfq_auction_id')) {
                    $totalWhere['rfq_auction_id'] = $auction->id;
                }

                $existsTotal = DB::table('rfq_vendor_auction_price_total')
                    ->where($totalWhere)->lockForUpdate()->first();

                if ($existsTotal) {
                    DB::table('rfq_vendor_auction_price_total')
                        ->where($totalWhere)
                        ->update(['total_price'=>$lotPrice,'updated_at'=>now()]);
                } else {
                    DB::table('rfq_vendor_auction_price_total')
                        ->insert(array_merge($totalWhere,[
                            'total_price'=>$lotPrice,
                            'created_at'=>now(),
                            'updated_at'=>now()
                        ]));
                }
            }

            // (B) Upsert per-variant unit price into rfq_vendor_auction_price
            foreach ($variants as $v) {
                $originalPrice  = (float) $v->start_price;
                $adjustedPrice  = $originalPrice - ($originalPrice * ($adjustmentPercent / 100));
                $vendPrice      = $originalPrice > 0 ? round($adjustedPrice, 2) : 0.00;
                if ($vendPrice < 0) {
                    $vendPrice = 0.00;
                }

                $specTxt = null;
                if (array_key_exists($v->rfq_variant_id, $vendSpecs)) {
                    $tmp = trim((string)$vendSpecs[$v->rfq_variant_id]);
                    $specTxt = ($tmp === '') ? null : mb_substr($tmp, 0, 10000);
                }

                $rowWhere = [
                    'rfq_no'                 => $rfqId,
                    'rfq_auction_id'         => $auction->id,
                    'vendor_id'              => $vendorId,
                    'rfq_product_veriant_id' => (int) $v->rfq_variant_id,
                ];

                $payload = [
                    'vend_price'           => $vendPrice,
                    'vend_specs'           => $specTxt,
                    'vend_price_basis'     => $vend_price_basis,
                    'vend_payment_terms'   => $vend_payment_terms,
                    'vend_delivery_period' => $vend_delivery_period !== null ? (int)$vend_delivery_period : null,
                    'vend_price_validity'  => ($vend_price_validity === '' ? null : (float)$vend_price_validity),
                    'vend_dispatch_branch' => $vend_dispatch_branch !== null ? (int)$vend_dispatch_branch : null,
                    'vend_currency'        => $vend_currency,
                    'vendor_user_id'       => auth()->id() ?? $vendorId,
                    'updated_at'           => now(),
                ];

                $existing = DB::table('rfq_vendor_auction_price')
                    ->where($rowWhere)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    DB::table('rfq_vendor_auction_price')
                        ->where($rowWhere)
                        ->update($payload);
                } else {
                    DB::table('rfq_vendor_auction_price')
                        ->insert(array_merge($rowWhere, $payload, ['created_at'=>now()]));
                }
            }
        });

        return response()->json(['status'=>true,'message'=>'Saved successfully.']);
    }

    /**
     * Return L1 total price and rank for the current vendor in the latest auction.
     */
    public function liveMetricsTotal(Request $request)
    {
        $request->validate([
            'rfq_id' => ['required', 'string'],
        ]);

        $rfqId    = (string) $request->input('rfq_id');
        $vendorId = (int) (function_exists('getParentUserId') ? getParentUserId() : (auth()->id() ?? 0));
        if (!$vendorId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $auction = $this->latestAuctionRow($rfqId);
        if (!$auction) {
            return response()->json(['status' => false, 'message' => 'Auction not found'], 404);
        }

        $hasAuctionIdCol = Schema::hasColumn('rfq_vendor_auction_price_total', 'rfq_auction_id');

        $sub = DB::table('rfq_vendor_auction_price_total')
            ->selectRaw('MAX(id) as max_id, vendor_id')
            ->where('rfq_no', $rfqId);
        if ($hasAuctionIdCol) {
            $sub->where('rfq_auction_id', $auction->id);
        }
        $sub->groupBy('vendor_id');

        $rowsQuery = DB::table('rfq_vendor_auction_price_total as t')
            ->joinSub($sub, 's', 's.max_id', '=', 't.id')
            ->select('t.vendor_id', 't.total_price');
        if ($hasAuctionIdCol) {
            $rowsQuery->where('t.rfq_auction_id', $auction->id);
        }
        $rows = $rowsQuery->get();

        if ($rows->isEmpty()) {
            return response()->json(['status' => true, 'data' => ['l1' => null, 'rank' => null, 'vendorPrice' => null]]);
        }

        $l1 = $rows->min('total_price');
        $vendorPrice = optional($rows->firstWhere('vendor_id', $vendorId))->total_price;

        $rank = null;
        if ($vendorPrice !== null) {
            $distinct = $rows->pluck('total_price')
                ->map(fn($v) => (float) $v)
                ->unique()
                ->sort()
                ->values();
            foreach ($distinct as $i => $price) {
                if (bccomp((string) $price, (string) $vendorPrice, 2) === 0) {
                    $rank = $i + 1;
                    break;
                }
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'l1'          => $l1 !== null ? round((float) $l1, 2) : null,
                'rank'        => $rank,
                'vendorPrice' => $vendorPrice !== null ? round((float) $vendorPrice, 2) : null,
            ],
        ]);
    }

    /**
     * Validate bid price against L1 or start price with decrement rules.
     */
    private function checkRankByPrice($rfq_id, $vend_id, $total_price, $total_bid_price, $min_bid_decrement = 0)
    {
        $result = ['status' => 1, 'message' => ''];

        if (empty($rfq_id) || empty($vend_id)) {
            return ['status' => 7, 'message' => 'Something went wrong!'];
        }

        $total_price = round((float) $total_price, 2);
        $max_decrement = 10; // max 10% decrement allowed

        $l1_row = DB::table('rfq_vendor_auction_price_total')
            ->where('rfq_no', $rfq_id)
            ->orderBy('total_price', 'asc')
            ->select('vendor_id', 'total_price')
            ->first();

        if ($l1_row) {
            $l1_price  = round((float) $l1_row->total_price, 2);
            $l1_vendor = (int) $l1_row->vendor_id;

            if ($total_price == $l1_price && $vend_id != $l1_vendor) {
                return [
                    'status' => 3,
                    'message' => request()->input('vendor_currency') . $total_price . ' already submitted by another vendor as Rank 1. Please enter a lower amount.'
                ];
            }

            if ($total_price > $l1_price) {
                return [
                    'status' => 2,
                    'message' => 'You cannot enter a price higher than the L1 price ' . request()->input('vendor_currency') . $l1_price . '.'
                ];
            }

            if ($total_price < $l1_price && $min_bid_decrement > 0) {
                $expected_min_price = round($l1_price - ($l1_price * ($min_bid_decrement / 100)), 2);
                if ($total_price > $expected_min_price) {
                    return [
                        'status' => 5,
                        'message' => 'Price must be ≤ ' . request()->input('vendor_currency') . $expected_min_price . ' (minimum bid decrement ' . $min_bid_decrement . '%)'
                    ];
                }
            }

            $effective_decrement = $min_bid_decrement + $max_decrement;
            if ($total_price < $l1_price && $effective_decrement > 0) {
                $expected_min_price = round($l1_price - ($l1_price * ($effective_decrement / 100)), 2);
                if ($total_price < $expected_min_price) {
                    return [
                        'status' => 6,
                        'message' => 'Price cannot be reduced by more than 10% at once.'
                    ];
                }
            }
        } else {
            $effective_decrement = min($min_bid_decrement, $max_decrement);
            $total_bid_price    = round((float) $total_bid_price, 2);

            if ($total_price > $total_bid_price) {
                return [
                    'status' => 2,
                    'message' => 'You cannot enter a price higher than the start price ' . request()->input('vendor_currency') . $total_bid_price . '.'
                ];
            }

            if ($effective_decrement > 0) {
                $expected_min_price = round($total_bid_price - ($total_bid_price * ($effective_decrement / 100)), 2);
                if ($total_price < $expected_min_price) {
                    return [
                        'status' => 6,
                        'message' => 'Price cannot be reduced by more than 10% from start price at once.'
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * % adjustment between bid_price and total_price (CI-style).
     */
    private function calculate_price_adjustment($bid_price, $total_price)
    {
        $bid  = (float) $bid_price;
        $tot  = (float) $total_price;
        if ($bid > 0 && $tot > 0 && $tot <= $bid) {
            return (($bid - $tot) / $bid) * 100;
        }
        return 0;
    }

    /**
     * Helper: latest auction row for an RFQ.
     */
    private function latestAuctionRow(string $rfqId): ?object
    {
        return DB::table('rfq_auctions')
            ->where('rfq_no', $rfqId)
            ->orderByDesc('id')
            ->first();
    }
}
