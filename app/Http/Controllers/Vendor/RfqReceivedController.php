<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\RfqProductVariant; // Assuming this model exists
use App\Models\RfqVendorQuotation; // Assuming this model exists
use App\Models\RfqBuyerCounter; // Assuming this model exists
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RfqReceivedController extends Controller
{
    public function index(Request $request)
    // Fetches the latest RFQ per rfq_id (record_type = 2), de-duplicates, and applies filters for the vendor's received RFQs
    {
        // 1) latest row per rfq_id (record_type = 2)
        // Subquery: Get the latest row per rfq_id where record_type = 2
        $latestPerRfqId = DB::table('rfqs as r2')
            ->selectRaw('MAX(r2.id) as id')
            ->where('r2.record_type', 2)
            ->where('r2.buyer_rfq_status', "!=", 2)
            ->groupBy('r2.rfq_id');

        $vendorId = getParentUserId();

        $query = Rfq::query()
            ->from('rfqs')
            // Only keep latest rfqs.id for each rfq_id (de-duplication)
            ->whereIn('rfqs.id', $latestPerRfqId)
            ->where('rfqs.record_type', 2)
            ->where('rfqs.buyer_rfq_status', "!=", 2)
            // Use WHERE EXISTS instead of JOIN to avoid row multiplication (duplicates)
            ->whereExists(function ($q) use ($vendorId, $request) {
                $q->select(DB::raw(1))
                  ->from('rfq_vendors as rv')
                  ->whereColumn('rv.rfq_id', 'rfqs.rfq_id')
                  ->where('rv.vendor_user_id', $vendorId);

                // Optional vendor_status filter (from rfq_vendors)
                if ($request->filled('status')) {
                    $q->where('rv.vendor_status', $request->status);
                }
            })
            // Eager load relationships for table display
            ->with([
                'rfqVendors',
                'rfqProducts',
                'rfqProducts.masterProduct',
                'buyer'
            ])
            // Only select rfqs columns to prevent row multiplication
            ->select('rfqs.*');

        // Filters on parent
        // Filter by buyer name if provided
        if ($request->filled('buyer_name')) {
            $legal_name = $request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%{$legal_name}%");
            });
        }

        // Filter by RFQ number if provided
        if ($request->filled('frq_no')) {
            $query->where('rfqs.rfq_id', 'like', "%{$request->frq_no}%");
        }

        // Ordering
        // Ordering logic for DataTables (or default)
        $order = $request->order;
        if (!empty($order)) {
            $columns = [
                0 => 'rfqs.id',
                1 => 'rfqs.rfq_id',
                2 => 'rfqs.created_at',
                3 => 'rfqs.last_response_date',
                4 => 'rfqs.buyer_rfq_status',
            ];
            $colIdx = (int)($order[0]['column'] ?? 0);
            $dir = in_array(strtolower($order[0]['dir'] ?? 'desc'), ['asc','desc']) ? $order[0]['dir'] : 'desc';
            $orderCol = $columns[$colIdx] ?? 'rfqs.id';
            $query->orderBy($orderCol, $dir);
        } else {
            $query->orderBy('rfqs.id', 'desc');
        }

        // Pagination
        $perPage = (int)$request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        $rfq_for_order = [];
        foreach ($results as $rfq) {
            if(in_array($rfq->rfqVendors->first()->vendor_status, [5, 10])){
                $rfq_for_order[] = $rfq->rfq_id;
            }
        }

        $orders_id = [];
        if(!empty($rfq_for_order)){
            $orders_id = DB::table('orders')
                ->select(DB::raw('MAX(id) as id, rfq_id'))
                ->whereIn('rfq_id', $rfq_for_order)
                ->groupBy('rfq_id')
                ->pluck('id', 'rfq_id');

            unset($rfq_for_order);
        }

        // Return partial for AJAX or full view
        if ($request->ajax()) {
            return view('vendor.rfq-received.partials.table', compact('results', 'orders_id'))->render();
        }
        return view('vendor.rfq-received.index', compact('results', 'orders_id'));
    }


    public function showRfqReplyForm($rfq_id)
    // Shows the RFQ reply form for a vendor, including product, variant, branch, and currency info
    {
        $vendor_id = getParentUserId();
        $rfq = $this->getRfqDetails($rfq_id);
        if (!$rfq) {
            abort(404, 'RFQ not found');
        }
        $products = $this->getRfqProducts($rfq_id, $vendor_id);
        $variants = $this->getRfqVariantsGrouped($rfq_id);
        $branches = $this->getVendorBranches($vendor_id);
        $vendor_currency = $this->getVendorCurrencies();

        return view('vendor.rfq-received.rfq_details', compact(
            'rfq', 'products', 'variants', 'branches', 'vendor_currency'
        ));
    }
    // Fetches RFQ details with buyer info for display
    private function getRfqDetails($rfq_id)
    {
        return DB::table('rfqs as r')
            ->leftJoin('buyers as b', 'r.buyer_id', '=', 'b.user_id')
            ->leftJoin('users as u', 'r.buyer_user_id', '=', 'u.id')
            ->select(
                'r.*',
                'b.legal_name as buyer_legal_name',
                'u.name as buyer_user_name'
            )
            ->where('r.rfq_id', $rfq_id)
            ->first();
    }

    // Fetches all products for an RFQ, including vendor-specific info if available
    private function getRfqProducts_old($rfq_id, $vendor_id)
    {
        return DB::table('rfq_products as rp')
            ->join('products as p', 'rp.product_id', '=', 'p.id')
            ->Join('divisions as d', 'p.division_id', '=', 'd.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('vendor_products as vp', function ($join) use ($vendor_id) {
                $join->on('vp.product_id', '=', 'rp.product_id')
                     ->where('vp.vendor_id', '=', $vendor_id);
            })
            ->where('rp.rfq_id', $rfq_id)
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
    private function getRfqVariantsGrouped_old($rfq_id)
    {
        return DB::table('rfq_product_variants')
            ->where('rfq_id', $rfq_id)
            ->get()
            ->groupBy('product_id');
    }

    // private function getRfqVariantsGrouped($rfq_id, $vendor_id = null)
    // {
    //     $query = RfqProductVariant::with([
    //         'vendor_quotation' => function($q) use ($vendor_id){
    //             if ($vendor_id) $q->where('vendor_id', $vendor_id);
    //             $q->latest();
    //         },
    //         'buyer_counter_offers',     // <-- added
    //         'vendor_price_history',     // <-- added
    //     ])->where('rfq_id', $rfq_id);

    //     return $query->get()->groupBy('product_id');
    // }
    private function getRfqProducts($rfq_id, $vendor_id)
    {
        return DB::table('rfq_products as rp')
            ->join('products as p', 'rp.product_id', '=', 'p.id')
            ->join('divisions as d', 'p.division_id', '=', 'd.id')
            ->leftJoin('categories as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('vendor_products as vp', function ($join) use ($vendor_id) {
                $join->on('vp.product_id', '=', 'rp.product_id')
                    ->where('vp.vendor_id', '=', $vendor_id);
            })
            ->where('rp.rfq_id', $rfq_id)
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
                'vp.approval_status',
                DB::raw("CASE WHEN vp.id IS NULL THEN 'no' ELSE 'yes' END as is_product"),
                DB::raw("CASE WHEN vp.id IS NULL THEN '' ELSE '$vendor_id' END as vendor_id")
            )
            ->orderByDesc(DB::raw("CASE WHEN vp.id IS NULL THEN 0 ELSE 1 END")) // put is_product=yes on top
            ->get();
    }

    /**
     * Fetches all variants for an RFQ, grouped by product_id, with:
     * 1. Latest vendor quotation for each variant
     * 2. All buyer counter offers (history) for tooltips
     * 3. Vendor's own price history
     *
     * Also attaches latest values for easy Blade access.
     */
    private function getRfqVariantsGrouped(string $rfqId, ?int $vendorId = null)
    {
        // default to logged-in parent vendor
        $vendorId = $vendorId ?: getParentUserId();

        $variants = \App\Models\RfqProductVariant::with([
            // 1) Latest quotation for this vendor (inline price/mrp/discount/specs)
            'vendor_quotation' => function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->orderByDesc('id');      // latest row first
            },

            // 2) ALL buyer counter offers (history) for tooltip
            //    i.e., rfq_vendor_quotations rows for this vendor where buyer_price > 0
            'buyer_counter_offers' => function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->whereNotNull('buyer_price')
                  ->where('buyer_price', '>', 0)
                  ->orderByDesc('id')
                  ->select([
                      'id', 'rfq_id', 'vendor_id', 'rfq_product_variant_id',
                      'buyer_price', 'created_at','updated_at'
                  ]);
            },

            // 3) Vendor price history (your own sent prices history)
            'vendor_price_history' => function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                  ->orderByDesc('id')
                  ->select([
                      'id', 'rfq_id', 'vendor_id', 'rfq_product_variant_id',
                      'price', 'created_at'
                  ]);
            },
        ])
        ->where('rfq_id', $rfqId)
        ->get();

        // Attach latest (first) entries for easy Blade usage (optional)
        $variants->each(function ($v) {
            $v->latest_vendor_price   = optional($v->vendor_quotation)->price;
            $v->latest_vendor_mrp     = optional($v->vendor_quotation)->mrp;
            $v->latest_vendor_discount= optional($v->vendor_quotation)->discount;
            $v->latest_vendor_specs   = optional($v->vendor_quotation)->specification;

            $v->latest_buyer_counter  = optional(collect($v->buyer_counter_offers)->first())->buyer_price;
            $v->latest_hist_price     = optional(collect($v->vendor_price_history)->first())->price;
        });

        return $variants->groupBy('product_id');
    }


    // Fetches all active branches for a vendor
    private function getVendorBranches($vendor_id)
    {
        return DB::table('branch_details')
            ->where('user_type', 2)
            ->where('user_id', $vendor_id)
            ->where('status', 1)
            ->get();
    }
    // Fetches all active currencies
    private function getVendorCurrencies()
    {
        return DB::table('currencies')->where('status', 1)->get();
    }
    /**
     * Handles vendor's submission of RFQ quotation (save as draft or submit).
     * Validates input, handles file uploads, manages draft/submit logic, and updates statuses.
     */
    public function submitRfq(Request $request)
    {
        // Basic validation (same as your original)
        // Validate required fields
        $validator = Validator::make($request->all(), [
            'vendor_dispatch_branch' => 'required|integer',
            'vendor_payment_terms'   => 'required|string',
            'vendor_price_basis'     => 'required|string',
            'vendor_delivery_period' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 0,
                'message' => $validator->errors()->first(),
            ]);
        }

        $rfq_id         = $request->input('rfq_id');
        // Prevent submission if auction is live or closed
        $auctionStatus  = $this->getAuctionStatus($rfq_id);
        if ($auctionStatus === 1 || $auctionStatus === 2) {
            return response()->json([
                'status'       => false,
                'pagetype'     => 1,
                'redirect_url' => url('sysadmin/orders'),
                'message'      => 'You cannot quote. The RFQ is either Closed or Auction is scheduled.',
            ]);
        }

    // Ids / actors
    $vendor_id       = getParentUserId();   // Vendor org id (for quotations table)
    $vendor_user_id  = auth()->id();        // Vendor user id (for rfq_vendors)
    $buyer_user_id   = $request->input('buyer_user_id');

    // Intent: 'save' (draft) or 'submit' (final)
    $action = $request->input('action');            // 'save' or 'submit'
    $status = ($action === 'save') ? '2' : '1';     // 2 = draft, 1 = submitted

    // Arrays from form (indexed by variantId)
    $prices      = $request->input('price', []);
    $mrps        = $request->input('mrp', []);
    $discounts   = $request->input('disc', []);
    $specs       = $request->input('vendor_spec', []);
    $sellerbrand = $request->input('sellerbrand', []);

        DB::beginTransaction();
        try {
            foreach ($prices as $variantId => $price) {
                if ($price === null || $price === '') {
                    continue;
                }

                // Find latest existing quotation (to keep old attachment if not replaced)
                $existingQuotation = RfqVendorQuotation::where([
                    'vendor_id'               => $vendor_id,
                    'rfq_product_variant_id'  => $variantId,
                ])->latest()->first();

                $attachmentPath = $existingQuotation->vendor_attachment_file ?? null;

                // Handle file upload (expects input name: vendor_attachment[<variantId>])
                if ($request->hasFile("vendor_attachment.$variantId")) {
                    $file = $request->file("vendor_attachment.$variantId");

                    // Delete old file if exists
                    if ($attachmentPath && file_exists(public_path('uploads/rfq_product/sub_products/' . $attachmentPath))) {
                        @unlink(public_path('uploads/rfq_product/sub_products/' . $attachmentPath));
                    }

                    $uploadPath = public_path('uploads/rfq_product/sub_products');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    $fileName = 'rfq_' . time() . '_' . $variantId . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadPath, $fileName);
                    $attachmentPath = $fileName;
                }

                // If saving draft, remove previous drafts for this variant (status = 2)
                if ($action === 'save') {
                    RfqVendorQuotation::where([
                        'vendor_id'              => $vendor_id,
                        'rfq_product_variant_id' => $variantId,
                        'status'                 => '2',
                    ])->delete();
                }

                // Create a new quotation row for this variant
                RfqVendorQuotation::create([
                    'vendor_id'                 => $vendor_id,
                    'rfq_id'                    => $rfq_id,
                    'rfq_product_variant_id'    => $variantId,
                    'price'                     => $price,
                    'mrp'                       => $mrps[$variantId] ?? 0,
                    'discount'                  => $discounts[$variantId] ?? 0,
                    'buyer_price'               => 0,
                    'specification'             => $specs[$variantId] ?? null,
                    'vendor_attachment_file'    => $attachmentPath,
                    'vendor_brand'              => $sellerbrand[$variantId] ?? null,
                    'vendor_remarks'            => $request->input('seller-remarks'),
                    'vendor_additional_remarks' => $request->input('Seller-Additional-Remarks'),
                    'vendor_price_basis'        => $request->input('vendor_price_basis'),
                    'vendor_payment_terms'      => $request->input('vendor_payment_terms'),
                    'vendor_delivery_period'    => $request->input('vendor_delivery_period'),
                    'vendor_price_validity'     => $request->input('vendor_price_validity'),
                    'vendor_dispatch_branch'    => $request->input('vendor_dispatch_branch'),
                    'vendor_currency'           => $request->input('vendor_currency'),
                    'buyer_user_id'             => $buyer_user_id,
                    'vendor_user_id'            => $vendor_user_id,
                    'status'                    => $status,
                    'created_at'                => now(),
                    'updated_at'                => now(),
                ]);
            }

            /**
             * ====== Status branching after SUBMIT (not for SAVE) ======
             * Buyer status -> rfqs.buyer_rfq_status (+ mark read)
             * Vendor status -> rfq_vendors.vendor_status (by rfq_id + vendor_user_id)
             *
             * Logic matches CI update_rfq_statuses() semantics.
             */
            if ($action !== 'save') {
                // Get current statuses
                $buyerSide = DB::table('rfqs')
                    ->select('buyer_id', 'buyer_user_id', 'buyer_rfq_status')
                    ->where('rfq_id', $rfq_id)
                    ->first();

                $vendorSide = DB::table('rfq_vendors')
                    ->select('vendor_status')
                    ->where('rfq_id', $rfq_id)
                    ->where('vendor_user_id', $vendor_user_id)
                    ->orderByDesc('id')
                    ->first();

                $buyer_rfq_status = (int)($buyerSide->buyer_rfq_status ?? 0);
                $vend_rfq_status  = (int)($vendorSide->vendor_status ?? 0);

                // Status update logic (mirrors CI logic)
                if ($buyer_rfq_status != 9) {
                    // If vendor status is 4 or 6, set both to 6
                    if ($vend_rfq_status == 4 || $vend_rfq_status == 6){
                        $this->updateRfqStatuses(6, 6, $rfq_id, $vendor_user_id);
                    // If vendor status not 4, buyer status is 6 and not 9, set vendor to 7, buyer to 6
                    }elseif($vend_rfq_status != 4 && $buyer_rfq_status == 6 && $buyer_rfq_status != 9){
                        $this->updateRfqStatuses(7, 6, $rfq_id, $vendor_user_id);
                    // Otherwise, set both to 7
                    }elseif($vend_rfq_status != 9) {
                        $this->updateRfqStatuses(7, 7, $rfq_id, $vendor_user_id);
                    }
                } else {
                    // If vendor status is 4 or 6, set vendor to 6, buyer to 9
                    if ($vend_rfq_status == 4 || $vend_rfq_status == 6){
                        $this->updateRfqStatuses(6, 9, $rfq_id, $vendor_user_id);
                    // If vendor status not 4, buyer status is 6 and not 9, set vendor to 7, buyer to 9
                    }elseif($vend_rfq_status != 4 && $buyer_rfq_status == 6 && $buyer_rfq_status != 9){
                        $this->updateRfqStatuses(7, 9, $rfq_id, $vendor_user_id);
                    // If vendor status not 9, set vendor to 7, buyer to 9
                    } elseif ($vend_rfq_status != 9) {
                        $this->updateRfqStatuses(7, 9, $rfq_id, $vendor_user_id);
                    // If both are 9, set both to 9
                    } elseif ($vend_rfq_status == 9 && $buyer_rfq_status == 9) {
                        $this->updateRfqStatuses(9, 9, $rfq_id, $vendor_user_id);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'RFQ ' . ($status === '2' ? 'saved' : 'submitted') . ' successfully!',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            // Log error if you want: \Log::error($e);
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong while processing your quotation.',
            ], 500);
        }
    }

    /**
     * Private function only for use inside this controller
     */
    /**
     * Returns auction status for an RFQ:
     * 1 = Auction live or within pre-start window
     * 2 = Auction closed
     * '' = Auction yet to start or no auction record
     *
     * Always uses Asia/Kolkata timezone.
     */
    private function getAuctionStatus($rfqNo)
    {
        $auction = DB::table('rfq_auctions')
            ->select('auction_date', 'auction_start_time', 'auction_end_time')
            ->where('rfq_no', $rfqNo)
            ->first();

        if (!$auction) {
            return ''; // No auction record for this RFQ
        }

        // Always use Asia/Kolkata timezone
        $currentTime = Carbon::now('Asia/Kolkata');

        // Combine date & time, adjust start time by -1 hour (pre-start window)
        $startTime = Carbon::parse(
            $auction->auction_date . ' ' . $auction->auction_start_time,
            'Asia/Kolkata'
        )->subHour();

        $endTime = Carbon::parse(
            $auction->auction_date . ' ' . $auction->auction_end_time,
            'Asia/Kolkata'
        );

        // 1 = Auction live or within pre-start window
        if ($currentTime->between($startTime, $endTime)) {
            return 1;
        // 2 = Auction closed
        } elseif ($currentTime->greaterThanOrEqualTo($endTime)) {
            return 2;
        }

        // '' = Auction yet to start
        return '';
    }

    /**
     * Update buyer & vendor statuses (rfqs + rfq_vendors)
     * Matches CI update_rfq_statuses() semantics
     */
    /**
     * Updates buyer & vendor statuses (rfqs + rfq_vendors)
     * Matches CI update_rfq_statuses() semantics.
     *
     * @param int $vendorStatus New status for vendor (rfq_vendors)
     * @param int $buyerStatus  New status for buyer (rfqs)
     * @param string $rfqId     RFQ ID
     * @param int $vendorUserId Vendor user ID
     * @return bool             True if buyer status updated
     */
    private function updateRfqStatuses(int $vendorStatus, int $buyerStatus, string $rfqId, int $vendorUserId): bool
    {
        // Vendor-specific status (rfq_vendors by rfq_id + vendor_user_id)
        DB::table('rfq_vendors')->updateOrInsert(
            ['rfq_id' => $rfqId, 'vendor_user_id' => $vendorUserId],
            [
                'vendor_status' => $vendorStatus,
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        // Buyer-facing status (rfqs)
        $affectedBuyer = DB::table('rfqs')
            ->where('rfq_id', $rfqId)
            ->update([
                'buyer_rfq_status'      => $buyerStatus,
                'buyer_rfq_read_status' => 1,
                'updated_at'            => now(),
            ]);

        return (bool)$affectedBuyer;
    }



}

