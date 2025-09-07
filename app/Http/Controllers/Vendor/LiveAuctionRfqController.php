<?php

namespace App\Http\Controllers\Vendor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\RfqProductVariant;
use App\Models\RfqVendorQuotation;
use App\Models\RfqVendorAuction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;

class LiveAuctionRfqController extends Controller
{
    public function index(Request $request)
    // Build the base query for vendor auctions with eager loading
    // Filter by buyer name if provided
    // Filter by RFQ number if provided
    // Filter by auction date if provided
    // Handle ordering if requested (note: $column is undefined here, should be fixed in future)
    // Paginate results and return appropriate view
    {
        $query = RfqVendorAuction::where('vendor_id', getParentUserId())
            ->with([
                'rfq_auction',
                'rfq_auction.rfq',
                'rfq_auction.rfq_auction_variant',
                'rfq_auction.rfq_auction_variant.product',
                'rfq_auction.buyer',
                'rfq_auction.buyer.users'
            ]);
        if ($request->filled('buyer_name')) {
            $legal_name = $request->buyer_name;
            $query->whereHas('rfq_auction.buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('frq_no')) {
            $query->where('rfq_no', $request->frq_no);
        }
        if ($request->filled('auction_date')) {
            $query->whereHas('rfq_auction', function ($q) use ($request) {
                $q->where('auction_date', $request->auction_date);
            });
        }
        $order = $request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        if ($request->ajax()) {
            return view('vendor.live-auction.partials.table', compact('results'))->render();
        }
        return view('vendor.live-auction.index', compact('results'));
    }
    public function rfqAuctionOffer(string $rfqId = '')
    // Get current vendor user ID
    // Redirect if no RFQ ID or not logged in
    // Check if vendor is allowed in this auction
    // Get auction details and related buyer/user info
    // Compose auction status and meta info
    // Prepare data for the auction offer view
    {
        $vendId   =  getParentUserId();
        if (empty($rfqId)) {
            return Redirect::to(route('sysadmin.orders.index'));
        }
        if (!$vendId) {
            return Redirect::to(route('login'));
        }
        // Ensure this vendor is allowed in the auction (CI: rfq_vendor_auctions)
        $vendorAllowed = DB::table('rfq_vendor_auctions')
            ->where('rfq_no', $rfqId)
            ->where('vendor_id', $vendId)
            ->exists();
        if (!$vendorAllowed) {
            return Redirect::to(route('sysadmin.auction.live_auction_offer'));
        }
        // Parent user id (kept from your CI logic)
        $parentUserId =  getParentUserId();
        // Pull auction header (date/time/currency/decrement) from rfq_auctions
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfqId)
            ->orderByDesc('id')
            ->first();
        if (!$auction) {
            return Redirect::to(route('sysadmin.orders.index'));
        }
        $rfqData = DB::table('rfqs as r')
            ->join('rfq_vendors as rv', function ($j) use ($vendId) {
                $j->on('rv.rfq_id', '=', 'r.rfq_id')
                    ->where('rv.vendor_user_id', '=', $vendId);
            })
            ->where('r.rfq_id', $rfqId)
            ->where('r.record_type', 2)
            ->select('r.*', 'rv.id as rfq_vendor_id', 'rv.vendor_status', 'rv.vendor_user_id')
            ->orderByDesc('r.id')
            ->first();
        // Buyer facts (legal name, user’s name)
        $buyer = DB::table('buyers')->where('id', $auction->buyer_id)->first();
        $buyerUser = DB::table('users')->where('id', $auction->buyer_user_id)->first();
        // OPTIONAL: buyer branch name if you store it by id on the RFQ (leave null if not available)
        $branchName = null; // replace with your own resolver, if needed
        // Compose auction timing & status
        $auctionMeta = $this->computeAuctionStatus($auction);
        // Currency list for select
        $currencies = DB::table('currencies')->where('status', '1')->get();
        // Last total price for this vendor in the latest auction-shot (see helper below)
        $lastTotal = $this->lastPriceAuctionTotal($rfqId, $vendId);
        $lastTotalprice = $this->lastPriceAuctionByTotal($rfqId, $vendId);
        
        // Build view data (matching your CI array keys where possible)
        $data = [
            'page_title'             => 'RFQ Auction Details',
            'page_heading'           => 'RFQ Auction Details',
            'rfq_id'                 => $rfqId,
            'rfq'                    => $rfqData,
            'buyer_user_name'        => $buyerUser->name ?? '',
            'branch_name'            => $branchName,
            'buyer_legal_name'       => $buyer->legal_name ?? '',
            'last_rfq_price_data'    => '',
            'parent_u_id'            => $parentUserId,
            'products'               => $this->getProductDetailsByRfqId($rfqId),
            'variants'               => $this->getRfqVariantsGroupByRfqId($rfqId),
            'branches'               => $this->getVendorBranches($vendId),
            'refresh'                => $auctionMeta['refresh'],
            'auction_date'           => $auctionMeta['auction_date'],
            'auction_start_time'     => $auctionMeta['auction_start_time'],
            'auction_end_time'       => $auctionMeta['auction_end_time'],
            'auction_currency'       => $auctionMeta['auction_currency'],
            'min_bid_decrement'      => $auctionMeta['min_bid_decrement'],
            'current_status'         => $auctionMeta['current_status'],
            'vendor_currency'        => $currencies,
            'last_price'             => $lastTotal,
            'last_total_price'       => $lastTotalprice,
        ];
        // auction_type: '1' => Single Item Price (item-wise), '2' => Multiple Item Price (lot-wise per your earlier ask)
        if ($auction->auction_type === '2') {
            // LOT-WISE view
            return view('vendor.live-auction.auction-rfq-reply-lot-wise', $data);
        } else {
            // ITEM-WISE view (existing)
            return view('vendor.live-auction.auction-rfq-reply', $data);
        }
    }

    private function computeAuctionStatus(object $auction): array
    // Compute auction status (active, scheduled, ended) based on current time and auction times
    {
        $today      = Carbon::now('Asia/Kolkata')->toDateString();
        $nowTime    = Carbon::now('Asia/Kolkata')->format('H:i:s');

        $auctionDate       = $auction->auction_date;       // stored as 'YYYY-MM-DD' (string)
        $auctionStartTime  = $auction->auction_start_time; // 'HH:MM:SS'
        $auctionEndTime    = $auction->auction_end_time;   // 'HH:MM:SS'

        $isToday     = $auctionDate === $today;
        $isActive    = $isToday && ($nowTime >= $auctionStartTime && $nowTime <= $auctionEndTime);
        $isScheduled = ($auctionDate > $today) || ($isToday && $nowTime < $auctionStartTime);

        return [
            'refresh'           => $isActive ? 'yes' : 'no',
            'current_status'    => $isActive ? 1 : ($isScheduled ? 2 : 3),
            'auction_date'      => $auctionDate,
            'auction_start_time' => $auctionStartTime,
            'auction_end_time'  => $auctionEndTime,
            'auction_currency'  => $auction->currency,
            'min_bid_decrement' => $auction->min_bid_decrement,
        ];
    }

    private function lastPriceAuctionTotal(string $rfqId, int $vendorId): ?float
    // Get the latest auction shot ID for this vendor and RFQ
    // Sum the vendor's prices for the latest auction shot
    {
        $latestShotId = DB::table('rfq_vendor_auction_price')
            ->where('rfq_no', $rfqId)
            ->where('vendor_id', $vendorId)
            ->max('rfq_auction_id');

        if (!$latestShotId) {
            return null;
        }

        $sum = DB::table('rfq_vendor_auction_price')
            ->where('rfq_no', $rfqId)
            ->where('vendor_id', $vendorId)
            ->where('rfq_auction_id', $latestShotId)
            ->sum('vend_price');

        return $sum ? (float)$sum : null;
    }

    private function lastPriceAuctionByTotal(string $rfqId, int $vendorId): ?float
    {
        // Adjust 'id' to 'created_at' (or another column) if that's your "latest" indicator
        $lastTotal = DB::table('rfq_vendor_auction_price_total')
            ->where('rfq_no', $rfqId)
            ->where('vendor_id', $vendorId)
            ->orderByDesc('id')          // or ->latest('created_at')
            ->value('total_price');      // returns scalar or null

        return $lastTotal !== null ? (float) $lastTotal : null;
    }


    private function getProductDetailsByRfqId(string $rfqId)
    // Get product details for this RFQ, joined with vendor-specific info
    {
        $vendor_id = getParentUserId();
        return DB::table('rfq_products as rp')
            ->join('products as p', 'rp.product_id', '=', 'p.id')
            ->leftJoin('divisions as d', 'p.division_id', '=', 'd.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('vendor_products as vp', function ($join) use ($vendor_id) {
                $join->on('vp.product_id', '=', 'rp.product_id')
                    ->where('vp.vendor_id', '=', $vendor_id);
            })
            ->where('rp.rfq_id', $rfqId)
            ->select(
                'rp.id as rfq_product_id',
                'rp.product_id',
                'rp.brand',
                'rp.remarks',
                'p.product_name',
                'd.division_name',
                'c.category_name',
                'vp.id as vendor_product_id',
                'vp.image as vendor_product_image',
                'vp.model_no',
                'vp.catalogue',
                'vp.approval_status'
            )
            ->get();
    }

    private function getRfqVariantsGroupByRfqId_old(string $rfqId, ?int $auctionId = null)
    // Get all variants for this RFQ, with latest vendor price and auction info (old version)
    {
        $vendorId = getParentUserId();

        // Resolve latest auction id for this RFQ if not provided
        if (!$auctionId) {
            $auctionId = DB::table('rfq_auctions')
                ->where('rfq_no', $rfqId)
                ->orderByDesc('id')
                ->value('id');
        }

        $variants = RfqProductVariant::with([
            'masterProduct',
            'uoms',
            'latestVendorAuctionPrice' => function ($q) use ($vendorId, $auctionId, $rfqId) {
                $q->where('vendor_id', $vendorId)
                    ->where('rfq_no', $rfqId);
                if ($auctionId) {
                    $q->where('rfq_auction_id', $auctionId);
                }
            },
            'auctionVariant' => function ($q) use ($auctionId) {
                if ($auctionId) {
                    $q->where('auction_id', $auctionId);
                }
            },
        ])
            ->where('rfq_id', $rfqId)
            ->get();

        // Add convenience attributes
        $variants->each(function ($v) use ($auctionId, $rfqId) {
            $v->latest_vend_price   = optional($v->latestVendorAuctionPrice)->vend_price;
            $v->auction_start_price = optional($v->auctionVariant)->start_price;

            // Compute L1 Price (lowest vendor price for this variant in this auction)
            $v->l1_price = DB::table('rfq_vendor_auction_price')
                ->where('rfq_no', $rfqId)
                ->where('rfq_auction_id', $auctionId)
                ->where('rfq_product_veriant_id', $v->id)   // variant id
                ->min('vend_price'); // lowest price
        });

        return $variants->groupBy('product_id');
    }

    private function getRfqVariantsGroupByRfqId(string $rfqId, ?int $auctionId = null)
    // Get all variants for this RFQ, with latest vendor price and auction info (current version)
    // For each variant, compute latest price, specs, auction start price, L1 price, and vendor rank
    {
        $vendorId = getParentUserId();

        if (!$auctionId) {
            $auctionId = DB::table('rfq_auctions')
                ->where('rfq_no', $rfqId)
                ->orderByDesc('id')
                ->value('id');
        }

        $variants = RfqProductVariant::with([
            'masterProduct',
            'uoms',
            'latestVendorAuctionPrice' => function ($q) use ($vendorId, $auctionId, $rfqId) {
                $q->where('rfq_vendor_auction_price.vendor_id', $vendorId)
                  ->where('rfq_vendor_auction_price.rfq_no', $rfqId)
                  ->when($auctionId, fn($qq) => $qq->where('rfq_vendor_auction_price.rfq_auction_id', $auctionId))
                  ->select([
                      'rfq_vendor_auction_price.id',
                      'rfq_vendor_auction_price.rfq_no',
                      'rfq_vendor_auction_price.rfq_auction_id',
                      'rfq_vendor_auction_price.rfq_product_veriant_id', // (typo column in DB)
                      'rfq_vendor_auction_price.vendor_id',
                      'rfq_vendor_auction_price.vend_price',
                      'rfq_vendor_auction_price.vend_specs',
                  ])
                  ->orderByDesc('rfq_vendor_auction_price.id');
            },
            // IMPORTANT: this now matches on rfq_auction_variants.rfq_variant_id = variants.id
            'auctionVariant' => function ($q) use ($auctionId) {
                if ($auctionId) {
                    $q->select('id', 'auction_id', 'rfq_variant_id', 'start_price')
                      ->where('auction_id', $auctionId);
                }
            },
        ])
        ->where('rfq_id', $rfqId)
        ->get();

        // REMOVE these debug lines:
        // echo "<pre>"; print_r($variants); die();

        $variants->each(function ($v) use ($auctionId, $rfqId) {
            $v->latest_vend_price   = optional($v->latestVendorAuctionPrice)->vend_price;
            $v->latest_vend_specs   = optional($v->latestVendorAuctionPrice)->vend_specs;
            $v->auction_start_price = optional($v->auctionVariant)->start_price;  //  now populated

            // L1 (lowest latest price among vendors for this variant in this auction)
            $v->l1_price = DB::table('rfq_vendor_auction_price as ap')
                ->join(
                    DB::raw('(SELECT MAX(id) as max_id
                             FROM rfq_vendor_auction_price
                             WHERE rfq_no = ? AND rfq_auction_id = ? AND rfq_product_veriant_id = ?
                             GROUP BY vendor_id) t'),
                    't.max_id', '=', 'ap.id'
                )
                ->setBindings([$rfqId, $auctionId, $v->id])
                ->min('ap.vend_price');

            // Current vendor rank (dense rank by latest price)
            $v->vendor_rank = $this->computeDenseRankForVariant(
                $rfqId, $auctionId, $v->id, $v->latest_vend_price
            );
        });

        return $variants->groupBy('product_id');

    }

    private function computeDenseRankForVariant(string $rfqId, int $auctionId, int $variantId, ?float $vendorPrice): ?int
    // Compute the dense rank for a vendor's price among all vendors for a variant in this auction
    {
        if ($vendorPrice === null) {
            return null;
        }

        // Get each vendor’s latest submitted price for this variant in this auction
        $latestPrices = DB::table('rfq_vendor_auction_price as ap')
            ->join(
                DB::raw('(SELECT MAX(id) as max_id
                             FROM rfq_vendor_auction_price
                             WHERE rfq_no = ? AND rfq_auction_id = ? AND rfq_product_veriant_id = ?
                             GROUP BY vendor_id) t'),
                't.max_id',
                '=',
                'ap.id'
            )
            ->setBindings([$rfqId, $auctionId, $variantId])
            ->pluck('ap.vend_price')
            ->map(fn($v) => (float)$v)   // cast to float
            ->sort()                     // ascending: lowest first
            ->values();

        if ($latestPrices->isEmpty()) {
            return null;
        }

        // Build distinct sorted list
        $distinctPrices = $latestPrices->unique()->values();

        // Dense rank: find the index of vendorPrice
        foreach ($distinctPrices as $i => $price) {
            if (bccomp((string)$price, (string)$vendorPrice, 2) === 0) {  // 2 decimal precision compare
                return $i + 1;  // 1-based rank
            }
        }

        // If not matched (edge case), return "after last"
        return $distinctPrices->count() + 1;
    }


    private function getVendorBranches($vendId)
    // Get all active branches for this vendor
    {
        return DB::table('branch_details')
            ->where('user_type', 2)
            ->where('user_id', $vendId)
            ->where('status', 1)
            ->get();
    }

    public function submitAuctionPrice(Request $request)
    // Validate request and get vendor ID
    // Ensure vendor is allowed and auction is active
    // Collect input prices/specs and fetch server-side constraints
    // Validate each variant's bid against auction rules
    // Save prices and maybe extend auction tail if needed
    {
        // 1) Validate + basics
        $v          = $this->validateSubmitRequest($request);
        $vendorId   = getParentUserId();
        $rfqId      = $v['rfq_id'];

        $this->ensureVendorAllowed($rfqId, $vendorId);

        $auction    = $this->getCurrentAuction($rfqId);
        $this->ensureAuctionActive($auction);
        $this->ensureCurrencyRule($rfqId, $vendorId, $v['vendor_currency'] ?? null);

        // 2) Collect inputs
        [$prices, $specs, $variantIds] = $this->collectInputs($v);

        // 3) Read server-side constraints/snapshots
        $startPrices = $this->fetchStartPrices($auction->id, $variantIds);
        $l1ByVariant = $this->fetchL1ByVariant($rfqId, $auction->id, $variantIds);
        $myPrevRows  = $this->fetchMyPrevPrices($rfqId, $auction->id, $vendorId, $variantIds);

        // 4) Validate per-variant (server mirrors client)
        $minDecPct   = (float)($auction->min_bid_decrement ?? 0);
        $errors      = $this->validateVariantBids($prices, $startPrices, $l1ByVariant, $myPrevRows, $minDecPct);

        if (!empty($errors)) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => ['variants' => $errors],
            ], 422);
        }

        // 5) Save + tail extension
        try {
            DB::beginTransaction();

            $this->upsertVendorPrices(
                $rfqId,
                $auction->id,
                $vendorId,
                $prices,
                $specs,
                $v
            );

            $this->maybeExtendAuctionTail($auction, $rfqId, $vendorId, $variantIds);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Prices submitted successfully.',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Unable to submit prices right now.',
            ], 500);
        }
    }

    /* -------------------- MICRO HELPERS -------------------- */

    private function validateSubmitRequest(Request $request): array
    // Validate the auction price submission request
    {
        $rules = [
            'rfq_id'                 => ['required', 'string'],
            'price'                  => ['required', 'array', 'min:1'],
            'price.*'                => ['numeric', 'min:0.01'],
            'vendor_spec'            => ['sometimes', 'array'],
            'vendor_price_basis'     => ['required', 'string'],
            'vendor_payment_terms'   => ['required', 'string'],
            'vendor_delivery_period' => ['required', 'numeric', 'min:1'],
            'vendor_price_validity'  => ['nullable', 'numeric', 'min:0'],
            'vendor_dispatch_branch' => ['required'],
            'vendor_currency'        => ['nullable', 'string'],
            'action'                 => ['nullable', 'string'],
        ];
        return $request->validate($rules);
    }

    private function ensureVendorAllowed(string $rfqId, int $vendorId): void
    // Ensure the vendor is allowed to participate in this auction
    {
        $ok = DB::table('rfq_vendor_auctions')
            ->where('rfq_no', $rfqId)
            ->where('vendor_id', $vendorId)
            ->exists();

        if (!$ok) {
            abort(response()->json([
                'status' => false,
                'message' => 'You are not allowed to participate in this auction.'
            ], 403));
        }
    }

    private function getCurrentAuction(string $rfqId): object
    // Get the current auction for this RFQ, or abort if not found
    {
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfqId)
            ->orderByDesc('id')
            ->first();

        if (!$auction) {
            abort(response()->json([
                'status' => false,
                'message' => 'Invalid RFQ or Auction.'
            ], 400));
        }
        return $auction;
    }

    private function ensureAuctionActive(object $auction): void
    // Ensure the auction is currently active (within time window)
    {
        $tz       = 'Asia/Kolkata';
        $now      = Carbon::now($tz);
        $today    = $now->toDateString();
        $nowTime  = $now->format('H:i:s');

        $isToday  = ($auction->auction_date === $today);
        $isActive = $isToday && ($nowTime >= $auction->auction_start_time && $nowTime <= $auction->auction_end_time);

        if (!$isActive) {
            abort(response()->json([
                'status' => false,
                'message' => 'Auction is not active. Please submit during the live window.'
            ], 400));
        }
    }

    private function ensureCurrencyRule(string $rfqId, int $vendorId, ?string $currency): void
    // Enforce currency selection rules for vendor
    {
        $isDisabled = ($this->isCurrencyDisabledForVendor($rfqId, $vendorId) === true);
        if (!$isDisabled && empty($currency)) {
            throw ValidationException::withMessages(['vendor_currency' => 'Vendor Currency is required.']);
        }
    }

    private function collectInputs(array $v): array
    // Collect prices, specs, and variant IDs from validated input
    {
        $prices     = $v['price'];                 // [variantId => price]
        $specs      = $v['vendor_spec'] ?? [];     // [variantId => spec]
        $variantIds = array_map('intval', array_keys($prices));
        return [$prices, $specs, $variantIds];
    }

    private function fetchStartPrices(int $auctionId, array $variantIds): \Illuminate\Support\Collection
    // Fetch start prices for all variants in this auction
    {

        return DB::table('rfq_auction_variants')
            ->where('auction_id', $auctionId)
            ->whereIn('rfq_variant_id', $variantIds)
            ->pluck('start_price', 'rfq_variant_id'); // [variantId => start_price]
    }

    private function fetchL1ByVariant(string $rfqId, int $auctionId, array $variantIds): array
    // Fetch the L1 (lowest) price for each variant in this auction
    {
        $latestPerVendor = DB::table('rfq_vendor_auction_price')
            ->selectRaw('MAX(id) as max_id, rfq_product_veriant_id, vendor_id')
            ->where('rfq_no', $rfqId)
            ->where('rfq_auction_id', $auctionId)
            ->whereIn('rfq_product_veriant_id', $variantIds)
            ->groupBy('rfq_product_veriant_id', 'vendor_id');

        $rows = DB::table('rfq_vendor_auction_price as ap')
            ->joinSub($latestPerVendor, 't', 't.max_id', '=', 'ap.id')
            ->select('ap.rfq_product_veriant_id as variant_id', 'ap.vend_price')
            ->get()
            ->groupBy('variant_id');

        $out = [];
        foreach ($variantIds as $vid) {
            $bucket = $rows->get($vid);
            $out[$vid] = $bucket ? (float)$bucket->min('vend_price') : null;
        }
        return $out;
    }

    private function fetchMyPrevPrices(string $rfqId, int $auctionId, int $vendorId, array $variantIds): \Illuminate\Support\Collection
    // Fetch the vendor's previous prices for each variant in this auction
    {
        $myLatestIds = DB::table('rfq_vendor_auction_price')
            ->selectRaw('MAX(id) as max_id, rfq_product_veriant_id')
            ->where('rfq_no', $rfqId)
            ->where('rfq_auction_id', $auctionId)
            ->where('vendor_id', $vendorId)
            ->whereIn('rfq_product_veriant_id', $variantIds)
            ->groupBy('rfq_product_veriant_id');

        return DB::table('rfq_vendor_auction_price as ap')
            ->joinSub($myLatestIds, 'm', 'm.max_id', '=', 'ap.id')
            ->select('ap.rfq_product_veriant_id as variant_id', 'ap.vend_price')
            ->get()
            ->keyBy('variant_id'); // variant_id => row(vend_price)
    }

    private function validateVariantBids(
    // Validate each variant's bid for price, decrement, and cap rules
        array $prices,
        \Illuminate\Support\Collection $startPrices,
        array $l1ByVariant,
        \Illuminate\Support\Collection $myPrevRows,
        float $minDecPct
    ): array {
        $eq2 = fn($a, $b) => round((float)$a, 2) === round((float)$b, 2);

        $errors = [];
        foreach ($prices as $variantId => $vendPriceRaw) {
            $vendPrice = (float)$vendPriceRaw;
            if ($vendPrice <= 0) {
                $errors[$variantId] = 'Price must be greater than 0.';
                continue;
            }

            $startPrice = (float)($startPrices[$variantId] ?? 0.0);
            $l1Price    = $l1ByVariant[$variantId] ?? null;

            // Equal to my last price? allow; skip min-decrement
            $myPrev   = $myPrevRows->get($variantId);
            $sameOld  = $myPrev ? $eq2($vendPrice, $myPrev->vend_price) : false;

            // Cap: L1 else Start
            $cap = ($l1Price && $l1Price > 0) ? $l1Price : $startPrice;
            if ($cap > 0 && $vendPrice > $cap) {
                $errors[$variantId] = $l1Price
                    ? "Entered price cannot exceed the L1 price of {$l1Price}."
                    : "Entered price cannot exceed the Start Price of {$startPrice}.";
                continue;
            }

            // Min bid decrement (only if price changed AND L1 exists)
            if (!$sameOld && $l1Price && $l1Price > 0 && $minDecPct > 0) {
                $minAcceptable = round($l1Price - ($l1Price * $minDecPct / 100.0), 2);
                if ($vendPrice > $minAcceptable) {
                    $errors[$variantId] = "Bid must be at least {$minDecPct}% lower than L1. Max allowed is {$minAcceptable}.";
                    continue;
                }
            }
        }
        return $errors;
    }

    private function upsertVendorPrices(
    // Insert or update vendor prices for each variant, skipping redundant writes
        string $rfqId,
        int $auctionId,
        int $vendorId,
        array $prices,
        array $specs,
        array $validatedForm
    ): void {
        foreach ($prices as $variantId => $vendPriceRaw) {
            $vendPrice = (float)$vendPriceRaw;
            if ($vendPrice <= 0) continue;

            $exists = DB::table('rfq_vendor_auction_price')
                ->where('rfq_no', $rfqId)
                ->where('rfq_auction_id', $auctionId)
                ->where('vendor_id', $vendorId)
                ->where('rfq_product_veriant_id', $variantId)
                ->first();

            // Skip redundant write if same as last saved
            if ($exists && round((float)$exists->vend_price, 2) === round($vendPrice, 2)) {
                continue;
            }

            $data = [
                'rfq_no'                 => $rfqId,
                'rfq_auction_id'         => $auctionId,
                'vendor_id'              => $vendorId,
                'rfq_product_veriant_id' => $variantId,
                'vend_price'             => $vendPrice,
                'vend_specs'             => $specs[$variantId] ?? null,
                'vend_price_basis'       => $validatedForm['vendor_price_basis'],
                'vend_payment_terms'     => $validatedForm['vendor_payment_terms'],
                'vend_delivery_period'   => $validatedForm['vendor_delivery_period'],
                'vend_price_validity'    => $validatedForm['vendor_price_validity'] ?? null,
                'vend_dispatch_branch'   => $validatedForm['vendor_dispatch_branch'],
                'vend_currency'          => $validatedForm['vendor_currency'] ?? null,
                'vendor_user_id'         => auth()->id(),
                'updated_at'             => now(),
            ];

            if ($exists) {
                DB::table('rfq_vendor_auction_price')->where('id', $exists->id)->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('rfq_vendor_auction_price')->insert($data);
            }
        }
    }

    private function isCurrencyDisabledForVendor(string $rfqId, int $vendorId): bool
    // Determine if currency selection is disabled for this vendor/buyer pair
    {
        $rfq = DB::table('rfqs')->where('rfq_id', $rfqId)->first();
        if (!$rfq) return false;

        $isIntlVendor = (string) (is_national() ?? '0') === '1';                 // your helper
        $isIntlBuyer  = (string) (is_national_buyer($rfq->buyer_id) ?? '0') === '1'; // your helper
        return $isIntlVendor && $isIntlBuyer;
    }

    private function maybeExtendAuctionTail(object $auction, string $rfqId, int $vendorId, array $variantIds): void
    // If auction is about to end and vendor is in top 2 for any variant, extend auction by 2 minutes
    {
        $tz = 'Asia/Kolkata';

        // 1) Always re-read the latest auction timings from DB (avoid stale in-memory object)
        $fresh = DB::table('rfq_auctions')->where('id', $auction->id)->first();
        if (!$fresh) return;

        // 2) Build Carbon datetimes with the freshest values
        $now = Carbon::now($tz);

        try {
            $end = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $fresh->auction_date . ' ' . $fresh->auction_end_time,
                $tz
            );
        } catch (\Throwable $e) {
            // If parsing ever fails, safely bail
            return;
        }

        // 3) Compute signed seconds remaining (positive => future)
        // Using signed diff avoids absolute-diff pitfalls
        $secondsLeft = $now->diffInSeconds($end, false);

        // Guard: extend only if between 1 and 120 seconds remain
        if ($secondsLeft <= 0 || $secondsLeft > 120) {
            return;
        }

        // 4) Check rank after this submission; if any variant rank <= 2, extend once
        foreach ($variantIds as $vid) {
            $myPrice = DB::table('rfq_vendor_auction_price')
                ->where('rfq_no', $rfqId)
                ->where('rfq_auction_id', $fresh->id)
                ->where('vendor_id', $vendorId)
                ->where('rfq_product_veriant_id', (int)$vid)
                ->orderByDesc('id')
                ->value('vend_price');

            if ($myPrice === null) {
                continue;
            }

            $rank = $this->computeDenseRankForVariant($rfqId, (int)$fresh->id, (int)$vid, (float)$myPrice);

            if ($rank !== null && $rank <= 2) {
                // Extend by +2 minutes from the *current* fresh end
                $newEnd = (clone $end)->addMinutes(2);

                DB::table('rfq_auctions')
                    ->where('id', $fresh->id)
                    ->update([
                        'auction_date'     => $newEnd->toDateString(),
                        'auction_end_time' => $newEnd->format('H:i:s'),
                        'updated_at'       => now($tz),
                    ]);

                break; // extend only once per submission
            }
        }
    }

    /* -------------------- END MICRO HELPERS -------------------- */
    public function liveMetrics(Request $request)
    // Validate request and get vendor ID
    // Get latest auction ID for this RFQ
    // Fetch latest prices for all vendors for each variant
    // For each variant, compute L1, vendor price, and rank
    {
        $request->validate([
            'rfq_id'      => 'required|string',
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'integer',
        ]);

        $rfqId      = $request->input('rfq_id');
        $variantIds = $request->input('variant_ids');
        $vendorId   = getParentUserId(); // current vendor user
        if (!$vendorId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        // Latest auction for this RFQ (or you can pass $auctionId from page)
        $auctionRow = DB::table('rfq_auctions')
            ->where('rfq_no', $rfqId)
            ->orderByDesc('id')
            ->select('id', 'is_forcestop')
            ->first();

        if (!$auctionRow) {
            return response()->json(['status' => false, 'message' => 'Auction not found'], 404);
        }

        $auctionId    = $auctionRow->id;
        $isForcestop = (string)($auctionRow->is_forcestop ?? '2');

        // Subquery: fetch latest row id per (variant, vendor)
        $sub = DB::table('rfq_vendor_auction_price')
            ->selectRaw('MAX(id) as max_id, rfq_product_veriant_id, vendor_id')
            ->where('rfq_no', $rfqId)
            ->where('rfq_auction_id', $auctionId)
            ->whereIn('rfq_product_veriant_id', $variantIds)
            ->groupBy('rfq_product_veriant_id', 'vendor_id');

        // Join back to get latest prices (one row per vendor per variant)
        $rows = DB::table('rfq_vendor_auction_price as ap')
            ->joinSub($sub, 't', 't.max_id', '=', 'ap.id')
            ->select([
                'ap.rfq_product_veriant_id as variant_id',
                'ap.vendor_id',
                'ap.vend_price',
            ])
            ->get()
            ->groupBy('variant_id');

        $out = [];
        foreach ($variantIds as $vid) {
            $bucket = $rows->get($vid) ?? collect();

            if ($bucket->isEmpty()) {
                $out[$vid] = [
                    'l1'          => null,
                    'rank'        => null,
                    'vendorPrice' => null,
                ];
                continue;
            }

            // L1 = min latest price among vendors
            $l1 = $bucket->min('vend_price');

            // Current vendor latest price for this variant
            $vendorPrice = optional(
                $bucket->firstWhere('vendor_id', $vendorId)
            )->vend_price;

            // Dense rank (lowest price = 1)
            $distinct = $bucket->pluck('vend_price')
                ->map(fn($v) => (float)$v)
                ->unique()
                ->sort()
                ->values();

            $rank = null;
            if ($vendorPrice !== null) {
                foreach ($distinct as $i => $price) {
                    // compare at 2-decimal precision
                    if (bccomp((string)$price, (string)$vendorPrice, 2) === 0) {
                        $rank = $i + 1; // 1-based
                        break;
                    }
                }
            }

            $out[$vid] = [
                'l1'          => $l1 !== null ? round((float)$l1, 2) : null,
                'rank'        => $rank,
                'vendorPrice' => $vendorPrice !== null ? round((float)$vendorPrice, 2) : null,
            ];
        }

        return response()->json([
            'status'       => true,
            'is_forcestop' => $isForcestop,
            'data'         => $out,
        ]);
    }
}
