<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\LiveVendorProduct;
use App\Models\Rfq;
use App\Models\RfqProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DateTime;

class AuctionController extends Controller
{
    public function __construct()
    {
        
    }

    public function index(Request $request)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();

        $query = Rfq::query()
            ->from('rfqs')
            // LEFT JOIN rfq_auctions so RFQs without auctions still appear
            ->Join('rfq_auctions as ra', 'ra.rfq_no', '=', 'rfqs.rfq_id')
            ->select([
                'rfqs.*',
                DB::raw('ra.rfq_no as auction_rfq_no'),
                DB::raw('ra.auction_date'),
                DB::raw('ra.auction_start_time'),
                DB::raw('ra.auction_end_time'),
            ])
            // Auction status: 0=none, 1=upcoming, 2=live, 3=completed
            ->selectRaw("
                CASE
                    WHEN ra.rfq_no IS NULL THEN 0
                    WHEN STR_TO_DATE(CONCAT(ra.auction_date,' ', ra.auction_start_time), '%Y-%m-%d %H:%i:%s') <= NOW()
                         AND STR_TO_DATE(CONCAT(ra.auction_date,' ', ra.auction_end_time),   '%Y-%m-%d %H:%i:%s') >= NOW()
                        THEN 2
                    WHEN STR_TO_DATE(CONCAT(ra.auction_date,' ', ra.auction_end_time), '%Y-%m-%d %H:%i:%s') < NOW()
                        THEN 3
                    ELSE 1
                END as auction_status
            ")
            ->where('rfqs.buyer_id', getParentUserId())
            ->whereNotIn('rfqs.buyer_rfq_status', [2, 5, 8, 10])
            ->where('rfqs.record_type', 2)
            ->with([
                'rfqVendorQuotations' => function ($q) {
                    $q->where('status', 1);
                },
                'rfqProducts.masterProduct',
                'buyerUser',
                'buyerBranch' => function ($q) {
                    $q->where('user_type', 1);
                },
            ])
            ->addSelect([
                'rfq_response_received' => function ($q) {
                    $q->selectRaw('COUNT(DISTINCT vendor_id)')
                      ->from('rfq_vendor_quotations')
                      ->whereColumn('rfq_id', 'rfqs.rfq_id')
                      ->where('status', 1);
                }
            ]);

        // Limit by user's branches
        if (!empty($user_branch_id_only)) {
            $query->whereIn('buyer_branch', $user_branch_id_only);
        }

        // === Filter: RFQ No. (partial match) ===
        if ($request->filled('rfq_no')) {
            $query->where('rfqs.rfq_id', 'like', '%' . trim($request->rfq_no) . '%');
        }

        // === Filter: Product Name (via relation) ===
        if ($request->filled('product_name')) {
            $name = trim($request->product_name);
            $query->whereHas('rfqProducts.masterProduct', function ($q) use ($name) {
                $q->where('product_name', 'like', '%' . $name . '%');
            });
        }

        // === Filter: Auction Date (supports d/m/Y or Y-m-d) ===
        // === Filter: Auction Date (DB saves as YYYY-MM-DD) ===
        if ($request->filled('auction_date')) {
            $raw = trim($request->auction_date);
            $auctionDateYmd = null;

            // Try d/m/Y first
            try {
                $auctionDateYmd = \Carbon\Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback: parse (works for YYYY-MM-DD)
                try {
                    $auctionDateYmd = \Carbon\Carbon::parse(str_replace('/', '-', $raw))->format('Y-m-d');
                } catch (\Exception $e2) {}
            }

            if ($auctionDateYmd) {
                $query->where('ra.auction_date', $auctionDateYmd);
            }
        }


        $results = $query
            ->orderBy('rfqs.updated_at', 'DESC')
            ->paginate($request->input('per_page', 25))
            ->appends($request->all());

        if ($request->ajax()) {
            return view('buyer.auction.partials.table', compact('results'))->render();
        }

        // Only these three filters are in use now
        return view('buyer.auction.index', compact('results'));
    }


    # -------------------------------------------------------------------------
    # CREATE / UPDATE AUCTION (create_auction)
    # -------------------------------------------------------------------------
    public function createAuction(Request $request)
    {
        // 1) Validate incoming payload (matches your posted form)
        $validator = Validator::make($request->all(), [
            'vendor_ids'                  => ['required','array','min:1'],
            'vendor_ids.*'                => ['required','integer'],

            'auction_date'                => ['required','string'], // d/m/Y
            'auction_time'                => ['required','string'], // H:i:s

            'min_bid_decrement'           => ['required','numeric','gt:0','min:0.50','max:99'],
            'min_bid_currency'            => ['nullable','string'],

            'rfq_no'                      => ['required','string'],
            'current_status'              => ['nullable','integer'],
            'edit_id'                     => ['nullable'],

            'variants'                    => ['required','array','min:1'],
            'variants.*.product_name'     => ['required','string'],
            'variants.*.specification'    => ['nullable','string'],
            'variants.*.size'             => ['nullable','string'],
            'variants.*.quantity'         => ['required','numeric','gt:0'],
            'variants.*.start_price'      => ['required','numeric','gt:0'],
            'variants.*.variant_grp_id'   => ['required'],

            // NEW: Auction type (1=normal, 2=lot-wise)
            'auction_type'                => ['nullable','in:1,2'],
        ], [
            'vendor_ids.required'         => 'Please select at least one vendor.',
            'auction_date.required'       => 'Auction date is required.',
            'auction_time.required'       => 'Auction time is required.',
            'min_bid_decrement.required'  => 'Minimum bid decrement is required.',
            'min_bid_decrement.numeric'   => 'Minimum bid decrement must be a numeric value.',
            'rfq_no.required'             => 'RFQ number is required.',
            'variants.required'           => 'Please add at least one product variant.',
            'auction_type.in'             => 'Invalid auction type.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors(),
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // 2) Normalize inputs to your existing structure
        $variants = $request->input('variants', []);

        $prodNames       = [];
        $sizes           = [];
        $quantities      = [];
        $startPrices     = [];
        $variantGrpIds   = [];

        foreach ($variants as $row) {
            $prodNames[]     = (string)($row['product_name']   ?? '');
            $sizes[]         = (string)($row['size']           ?? '');
            $quantities[]    = (float) ($row['quantity']       ?? 0);
            $startPrices[]   = (float) ($row['start_price']    ?? 0);
            $variantGrpIds[] = (string)($row['variant_grp_id'] ?? '');
        }

        // NEW: auction_type with server-side enforcement for 20+ variants
        $auctionType =  $request->input('auction_type');
        if (is_countable($variants) && count($variants) >= 20) {
            $auctionType = 2; // force lot-wise if 20 or more variants
        }

        $input = [
            'auction_vend'      => array_map('intval', $request->input('vendor_ids', [])),
            'auction_date'      => $request->input('auction_date'),
            'auction_time'      => $request->input('auction_time'),
            'min_bid_decrement' => (float)$request->input('min_bid_decrement'),
            'min_bid_currency'  => $request->input('min_bid_currency', 'INR'),
            'rfq_no'            => $request->input('rfq_no'),
            'current_status'    => (int)$request->input('current_status', 0),
            'edit_id'           => $request->input('edit_id'),

            'prod_names'        => $prodNames,
            'sizes'             => $sizes,
            'quantities'        => $quantities,
            'start_prices'      => $startPrices,
            'variant_grp_ids'   => $variantGrpIds,
            'variants'          => $variants,

            // NEW: pass the normalized auction type forward
            'auction_type'      => $auctionType, // 1 = normal, 2 = lot-wise

            'userdata'          => Auth::check() ? ['users_id' => Auth::id()] : null,
        ];

        // 3) Vendors & product validation using your existing helpers
        $vendorDetails   = $this->getVendorDetails($input['auction_vend']);
        $missingProducts = $this->validateVendorProducts($input['auction_vend'], $input['prod_names'], $vendorDetails);

        if (!empty($missingProducts)) {
            return response()->json(['status' => 'error', 'messages' => $missingProducts], 422);
        }

        // 4) Date/time validation (>= now, valid formats)
        $dtString = $input['auction_date'].' '.$input['auction_time']; // d/m/Y H:i:s
        $auctionStart = DateTime::createFromFormat('d/m/Y H:i:s', $dtString);
        if (!$auctionStart) {
            return response()->json(['status' => 'error', 'message' => 'Invalid date/time format.'], 422);
        }
        if (!$this->validateAuctionDateTime($input['auction_date'], $input['auction_time'])) {
            return response()->json(['status' => 'error', 'message' => 'Auction date and time must not be in the past or invalid.'], 422);
        }

        if (in_array((int)$input['current_status'], [1, 3], true)) {
            return response()->json(['status' => 'error', 'message' => 'Auction already closed or Running. You cannot update it.'], 422);
        }

        // 5) Compute end time (30 mins)
        $auctionEnd = (clone $auctionStart)->modify('+30 minutes');


        // 6) Persist into new tables (returns auction_id on success)
        // Make sure your saveOrUpdateAuction() accepts & stores 'auction_type'
        // e.g., in the auctions table: column 'auction_type' TINYINT(1) or ENUM('1','2')
        $auctionId = $this->saveOrUpdateAuction($input, $auctionStart, $auctionEnd);

        if ($auctionId) {
            // vendors table needs auction_id now
            $this->updateAuctionVendors($auctionId, $input['rfq_no'], $input['auction_vend']);

            // Optionally send notifications
            // $this->sendAuctionNotifications($input, $vendorDetails, $auctionStart, $auctionEnd);

            return response()->json([
                'status'  => 'success',
                'message' => 'Auction created/updated successfully!',
                // 'redirect' => route('...'),
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Error processing auction.'], 500);
    }


    // ===== Helpers =====

    private function getVendorDetails(array $vendorIds)
    {
        if (empty($vendorIds)) return collect();
        return DB::table('vendors as v')
            ->join('users as u', 'v.user_id', '=', 'u.id')
            ->whereIn('v.user_id', $vendorIds)
            ->select('v.user_id', 'v.legal_name as legal_name', 'u.email as email')
            ->get();
    }

    private function validateVendorProducts(array $vendorIds, array $prodNames, $vendorDetails): array
    {
        $missing = [];
        $unique  = [];

        foreach ($vendorIds as $vid) {
            foreach ($prodNames as $pname) {
                $key = $vid.'|'.$pname;
                if (isset($unique[$key])) continue;

                $exists = DB::table('vendor_products as vp')
                    ->join('products as p', 'vp.product_id', '=', 'p.id')
                    ->where('vp.vendor_id', $vid)
                    ->where('p.product_name', $pname)
                    ->where('vp.vendor_status', 1)
                    ->where('vp.approval_status', 1)
                    ->value('vp.id');

                if (!$exists) {
                    foreach ($vendorDetails as $v) {
                        if ((int)$v->user_id === (int)$vid) {
                            $missing[]    = "The vendor <b>{$v->legal_name}</b> does not have this product <b>{$pname}</b>; please remove the vendor from the Auction.";
                            $unique[$key] = true;
                            break;
                        }
                    }
                }
            }
        }
        return $missing;
    }

    private function validateAuctionDateTime(string $date, string $time): bool
    {
        try {
            $start = Carbon::createFromFormat('d/m/Y H:i:s', $date.' '.$time);
        } catch (\Throwable $e) {
            return false;
        }
        return $start && $start->greaterThanOrEqualTo(Carbon::now());
    }

    /**
     * Save/Update to NEW schema:
     * - rfq_auctions (header)  -> returns $auctionId
     * - rfq_auction_variants   -> replace all for this auction_id
     */
    private function saveOrUpdateAuction(array $input, DateTime $start, DateTime $end)
    {
        $buyer = $this->getBuyerForRfq($input['rfq_no']);
        $buyerId = $buyer['buyer_id'];
        $buyerUserId = $buyer['buyer_user_id'];

        $auctionId = null;

        DB::beginTransaction();
        try {
            // 1) Upsert header row in rfq_auctions by rfq_no
            $existing = DB::table('rfq_auctions')->where('rfq_no', $input['rfq_no'])->first();

            $header = [
                'rfq_no'             => $input['rfq_no'],
                'buyer_id'           => $buyerId,
                'buyer_user_id'      => $buyerUserId,
                'auction_date'       => $start->format('Y-m-d'),   // store YYYY-MM-DD in varchar(20)
                'auction_start_time' => $start->format('H:i:s'),
                'auction_end_time'   => $end->format('H:i:s'),
                'min_bid_decrement'  => $input['min_bid_decrement'],
                'currency'           => $input['min_bid_currency'] ?? 'INR',
                'auction_type'       => $input['auction_type'],
                'updated_at'         => now(),
            ];

            if ($existing) {
                DB::table('rfq_auctions')->where('id', $existing->id)->update($header);
                $auctionId = (int)$existing->id;
            } else {
                $header['created_at'] = now();
                $auctionId = (int) DB::table('rfq_auctions')->insertGetId($header);
            }

            // 2) Replace variants for this auction
            $this->replaceAuctionVariants($auctionId, $input);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('saveOrUpdateAuction failed', [
                'rfq_no' => $input['rfq_no'] ?? null,
                'error'  => $e->getMessage(),
            ]);
            return false;
        }

        return $auctionId;
    }

    /**
     * Delete + insert vendors for this auction_id.
     */
    private function updateAuctionVendors(int $auctionId, string $rfqNo, array $vendorIds): void
    {
        DB::table('rfq_vendor_auctions')->where('auction_id', $auctionId)->delete();

        if (!empty($vendorIds)) {
            $rows = [];
            $now  = now();
            foreach ($vendorIds as $vid) {
                $rows[] = [
                    'rfq_no'     => $rfqNo,
                    'auction_id' => $auctionId,
                    'vendor_id'  => (int)$vid,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('rfq_vendor_auctions')->insert($rows);
        }
    }

    /**
     * Delete + insert variants for this auction_id.
     * product_id is resolved by product_name; rfq_variant_id = variant_grp_id.
     */
    private function replaceAuctionVariants(int $auctionId, array $input): void
    {
        DB::table('rfq_auction_variants')->where('auction_id', $auctionId)->delete();

        $variants = $input['variants'] ?? [];
        if (empty($variants)) return;

        // Build unique product name list to resolve product_id in one query
        $names = [];
        foreach ($variants as $v) {
            $n = trim((string)($v['product_name'] ?? ''));
            if ($n !== '') $names[$n] = true;
        }
        $productMap = $this->getProductIdMapByNames(array_keys($names)); // [name => id]
         


        $rows = [];
        $now  = now();

        foreach ($variants as $vid => $v) {
             
            $pname      = (string)($v['product_name'] ?? '');
            $productId  = $productMap[$pname] ?? null;
            if (!$productId) {
                Log::warning('Product not found while inserting rfq_auction_variants', [
                    'product_name' => $pname, 'auction_id' => $auctionId,
                ]);
                continue;
            }

            // Use the variant id from payload; DO NOT cast to int (avoids overflow/dup issue)
            $rfqVarId   = (string)($v['variant_grp_id'] ?? $vid);
            $startPrice = (float)($v['start_price'] ?? 0);
            $rfq_variant_id = $v['rfq_variant_id'] ?? 0;

            $rows[] = [
                'auction_id'     => $auctionId,
                'product_id'     => (int)$productId,
                'rfq_variant_id' => $rfq_variant_id,        // <-- no (int) cast
                'start_price'    => $startPrice,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('rfq_auction_variants')->insert($rows);
        }
    }


    /**
     * Resolve product ids by product_name in one shot.
     */
    private function getProductIdMapByNames(array $names): array
    {
        $names = array_values(array_unique(array_filter($names, fn($v) => trim((string)$v) !== '')));
        if (empty($names)) return [];

        $rows = DB::table('products')
            ->whereIn('product_name', $names)
            ->select('id', 'product_name')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->product_name] = (int)$r->id;
        }
        return $map;
    }


    /**
     * Get buyer_id / buyer_user_id from rfqs using rfq_id = $rfqNo.
     * Fallback to session-based helpers/Auth if RFQ record not found.
     */
    private function getBuyerForRfq(string $rfqNo): array
    {
        $buyerId = null;
        $buyerUserId = null;

        $rfq = DB::table('rfqs')->where('rfq_id', $rfqNo)->select('buyer_id','buyer_user_id')->first();
        if ($rfq) {
            $buyerId = $rfq->buyer_id;
            $buyerUserId = $rfq->buyer_user_id;
        }

        if (!$buyerId) {
            $buyerId = getParentUserId();
        }
        if (!$buyerUserId && Auth::check()) {
            $buyerUserId = Auth::id();
        }

        return [
            'buyer_id' => (int)$buyerId,
            'buyer_user_id' => (int)$buyerUserId,
        ];
    }

    private function sendAuctionNotifications(array $input, $vendorDetails, DateTime $start, DateTime $end): void
    {
        $buyerId = function_exists('getBuyerParentIdBySession') ? getBuyerParentIdBySession() : null;
        $rfqNo   = $input['rfq_no'];

        $buyerCompanyName = DB::table('buyer_details')->where('user_id', $buyerId)->value('legal_name');

        $notificationData = [
            'rfq_no'             => $rfqNo,
            'buyer_company_name' => $buyerCompanyName,
            'auction_date'       => $start->format('d/m/Y'),
            'auction_time'       => $start->format('H:i:s'),
            'message_type'       => 'rfq_auction',
            'vendors_user_id'    => collect($vendorDetails)->pluck('user_id')->all(),
        ];

        if (function_exists('send_bulk_notification_to_vendor_for_rfq')) {
            send_bulk_notification_to_vendor_for_rfq($notificationData);
        }

        $mailData  = function_exists('getSystemEmail') ? getSystemEmail('user-success-action') : [];
        $template  = $mailData[0]->content ?? '';
        if (function_exists('str_replace_vendor_email_data')) {
            $template = str_replace_vendor_email_data($template);
        }

        $adminMsg  = str_replace([
            '$action_date','$from_time','$to_time','$rfqdate','$rfq_date_formate','$rfq_number','$buyer_name','$website_url'
        ], [
            $start->format('d/m/Y'), $start->format('H:i:s'), $end->format('H:i:s'),
            date('d M Y'), date('d/m/Y'), $rfqNo, $buyerCompanyName, url('/')
        ], $template);

        $subject = "Auction has been Scheduled for RFQ No.: ".$rfqNo;
        $mails   = [];

        foreach ($vendorDetails as $vendor) {
            $msg     = str_replace('$vendor_name', $vendor->legal_name, $adminMsg);
            $mails[] = ['subject' => $subject, 'body' => $msg, 'to' => $vendor->email];
        }

        $this->sendEmailForActionDB($mails);
    }

    private function sendEmailForActionDB(array $mailArr): void
    {
        $buyerId = Auth::id();
        $rows = [];
        foreach ($mailArr as $value) {
            if (function_exists('isValidEmail') && isValidEmail($value['to'])) {
                $rows[] = [
                    'user_id'    => $buyerId,
                    'email'      => $value['to'],
                    'subject'    => $value['subject'],
                    'mail_data'  => $value['body'],
                    'created_at' => now(),
                ];
            }
        }
        if (!empty($rows)) {
            DB::table('tbl_mail_data')->insert($rows);
        }
    }

    # -------------------------------------------------------------------------
    # GET AUCTION (get_auction)
    # -------------------------------------------------------------------------
    public function getAuction(Request $request)
    {
        $rfqNo = $request->post('rfq_no');

        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfqNo)
            ->orderBy('id', 'ASC')
            ->get()
            ->toArray();

        if ($auction) {
            $auctionDate = $auction[0]->auction_date;

            $bookedTimes = DB::table('rfq_auctions')
                ->where('auction_date', $auctionDate)
                ->pluck('auction_start_time')
                ->toArray();

            $vendorIds = DB::table('tbl_live_auction_vendors')
                ->where('rfq_no', $rfqNo)
                ->pluck('vend_id')
                ->toArray();

            return response()->json([
                'status'       => 'success',
                'data'         => $auction,
                'booked_times' => $bookedTimes,
                'vendor_ids'   => $vendorIds,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No auction found.']);
    }

    # -------------------------------------------------------------------------
    # LIVE AUCTION RFQ LIST (live_auction_rfq)
    # -------------------------------------------------------------------------
    public function liveAuctionRfq(Request $request)
    {
        $limit = (int)($request->query('limit') ?? (config('app.website_pagination', 20)));
        $page  = (int)($request->query('page') ?? 1);
        $start = $limit * ($page - 1);

        $userBranchIdOnly = function_exists('getBuyerUserBranchIdOnly') ? getBuyerUserBranchIdOnly() : null;

        // Using your model methods would be ideal; replicating with DB is possible if needed.
        $orders = app('App\\Models\\AuctionModel')->get_active_rfq($limit, $start, false, $userBranchIdOnly);
        $total  = app('App\\Models\\AuctionModel')->get_active_rfq('', '', true, $userBranchIdOnly);

        $rfqIds = [];
        $branchIds = [];
        $userIds = [];
        foreach ($orders as $o) {
            $rfqIds[]    = $o->rfq_id;
            $branchIds[] = $o->buyer_branch;
            $userIds[]   = $o->buyer_user_id;
        }

        $rfqProducts   = app('App\\Models\\RfqModel')->get_rfq_product($rfqIds);
        $allBranchUnit = app('App\\Models\\RfqModel')->get_branch_name_by_branch_id($branchIds);
        $allDivCat     = function_exists('getAllNewDivisionCategory') ? getAllNewDivisionCategory() : [];
        $allUsers      = function_exists('get_all_user_details') ? get_all_user_details($userIds) : [];

        foreach ($orders as $k => $v) {
            $prod = $rfqProducts[$v->rfq_id] ?? ['products' => ''];
            $v->products              = $prod['products'] ?? '';
            $v->username              = $allUsers[$v->buyer_user_id] ?? '';
            $v->total_no_of_response  = function_exists('get_rfq_response_count') ? get_rfq_response_count($v->rfq_id) : 0;
            $v->branch_name           = $allBranchUnit[$v->buyer_branch] ?? '';
            $orders[$k]               = $v;
        }

        // Basic pagination data (Blade can render links)
        $data = [
            'orders'        => $orders,
            'division_list' => function_exists('get_active_divisions') ? get_active_divisions() : [],
            'page_title'    => 'Live Auction RFQs',
            'pageing_link'  => null, // If you want to use Laravel paginator, adapt to LengthAwarePaginator
            'total_pages'   => $start,
            'breadcrum'     => [
                'Home'       => url('/'),
                'My Account' => url('/user/myaccount'),
                'Auction'    => null,
            ],
        ];

        return view('user.live-auction-rfq', $data);
    }

    # -------------------------------------------------------------------------
    # CLOSE AUCTION (close_auction)
    # -------------------------------------------------------------------------
    public function closeAuction(Request $request)
    {
        $rfqNo = $request->post('rfq_no');

        $exists = DB::table('rfq_auctions')->where('rfq_no', $rfqNo)->first();

        if ($exists) {
            DB::table('rfq_auctions')->where('rfq_no', $rfqNo)->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    # -------------------------------------------------------------------------
    # BUYER PRODUCT LIST (buyer_product_list)
    # -------------------------------------------------------------------------
    public function buyerProductList($rfq)
    {
        $buyerUserId       = function_exists('getBuyerParentIdBySession') ? getBuyerParentIdBySession() : null;
        $userBranchIdOnly  = function_exists('getBuyerUserBranchIdOnly') ? getBuyerUserBranchIdOnly() : null;

        if (function_exists('isValidateBuyerRFQ') && !isValidateBuyerRFQ($rfq, $buyerUserId, $userBranchIdOnly)) {
            session()->flash('invalid_url', 'Invalid URL');
            return redirect('user/myaccount');
        }

        $data = app('App\\Models\\AuctionModel')->get_rfq_details($rfq);
        if (empty($data)) {
            return redirect('my_order/active_rfq');
        }

        $auction = $this->getLiveAuctionDetails($rfq);

        $data['page_title']         = 'Auction CIS Sheet';
        $data['breadcrum']          = ['Home'=>url('/'), 'My Account'=>url('user/myaccount'), 'CIS'=>null];
        $data['uom_list']           = function_exists('getUOMNameById') ? getUOMNameById() : [];
        $data['rfq_div_cat']        = function_exists('getDivisionCategoryByCatId') ? getDivisionCategoryByCatId($data['rfq']->cat_id) : null;
        $data['auction_date']       = $auction['date'];
        $data['auction_start_time'] = $auction['start'];
        $data['auction_end_time']   = $auction['end'];
        $data['refresh']            = $auction['is_live'] ? 'yes' : 'no';
        $data['current_status']     = $auction['status'];
        $data['rfq_id']             = $rfq;

        if ($data['current_status'] == 3 && !$this->isPriceMapExists($rfq)) {
            $this->insertFinalPrices($rfq);
        }

        $data['currency_list'] = DB::table('tbl_currency')->where('status', 1)->get();

        return view('user.auction_rfq_status', $data);
    }

    private function getLiveAuctionDetails($rfq): array
    {
        $auction = DB::table('rfq_auctions')->where('rfq_no', $rfq)->first();

        $today = date('Y-m-d');
        $now   = date('H:i:s');
        $status = 2; // Scheduled

        if ($auction) {
            $start = $auction->auction_start_time;
            $end   = $auction->auction_end_time;

            if ($auction->auction_date == $today) {
                if ($now >= $start && $now <= $end) {
                    $status = 1; // Active
                } elseif ($now > $end) {
                    $status = 3; // Closed
                }
            } elseif ($auction->auction_date < $today) {
                $status = 3;
            }

            return [
                'date'    => $auction->auction_date,
                'start'   => $start,
                'end'     => $end,
                'is_live' => ($status == 1),
                'status'  => $status,
            ];
        }

        return ['date'=>'','start'=>'','end'=>'','is_live'=>false,'status'=>2];
    }

    private function isPriceMapExists($rfq): bool
    {
        return DB::table('tbl_rfq_price_map')->where('rfq_no', $rfq)->exists();
    }

    private function insertFinalPrices($rfq): void
    {
        $rows = DB::table('tbl_rfq as r')
            ->join('tbl_rfq_auction_price as p', 'r.rfq_record_id', '=', 'p.rfq_record_id')
            ->where('r.rfq_id', $rfq)
            ->select('p.*')
            ->get();

        if ($rows->isEmpty()) return;

        DB::beginTransaction();
        try {
            foreach ($rows as $item) {
                $deliveryPeriod = (is_numeric($item->vend_delivery_period) && $item->vend_delivery_period >= 0)
                    ? (int)$item->vend_delivery_period : 0;

                $priceValidity  = (is_numeric($item->vend_price_validity) && $item->vend_price_validity >= 0)
                    ? min((int)round($item->vend_price_validity), 999) : 0;

                DB::table('tbl_rfq_price')->insert([
                    'rfq_record_id'           => $item->rfq_record_id,
                    'vend_price'              => $item->vend_price,
                    'vend_mrp'                => $item->vend_mrp,
                    'vend_discount'           => $item->vend_discount,
                    'buyer_price'             => $item->buyer_price,
                    'vend_specs'              => $item->vend_specs,
                    'vend_attachment_file'    => $item->vend_attachment_file,
                    'vend_brand'              => $item->vend_brand,
                    'vend_remarks'            => $item->vend_remarks,
                    'vend_additional_remarks' => $item->vend_additional_remarks,
                    'vend_price_basis'        => $item->vend_price_basis,
                    'vend_payment_terms'      => $item->vend_payment_terms,
                    'vend_delivery_period'    => $deliveryPeriod,
                    'vend_price_validity'     => $priceValidity,
                    'vend_dispatch_branch'    => $item->vend_dispatch_branch,
                    'buyer_user_id'           => $item->buyer_user_id,
                    'vend_user_id'            => $item->vend_user_id,
                    'is_sent'                 => $item->is_sent,
                    'updated_date'            => $item->updated_date,
                    'created_date'            => $item->created_date,
                ]);
            }

            if (DB::table('tbl_rfq_price')->where('rfq_record_id', $rows[0]->rfq_record_id ?? -1)->exists()) {
                DB::table('tbl_rfq_price_map')->insert(['rfq_no' => $rfq]);
            }

            DB::commit();
            // Optional: redirect/refresh handled in Blade
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('RFQ Final Price Insertion Failed: '.$e->getMessage());
        }
    }

    # -------------------------------------------------------------------------
    # EXPORT BUYER CIS SHEET (export_buyer_cis_sheet_new)
    # -------------------------------------------------------------------------
    public function exportBuyerCisSheetNew(Request $request, $rfq)
    {
        $venFromDate  = $request->query('from_date', '');
        $venToDate    = $request->query('to_date', '');
        $venLocation  = [];
        $favorite     = $request->query('favorite', []);
        $lastVendor   = $request->query('last_vendor', []);

        if ($request->filled('location')) {
            $venLocation = function_exists('getVendorsByStateId')
                ? getVendorsByStateId(['state_ids' => $request->query('location'), 'rfq_number' => $rfq])
                : [];
        }

        $searchVen = array_unique(array_merge((array)$venLocation, (array)$favorite, (array)$lastVendor));
        $isDateFilter = ($venFromDate !== '' || $venToDate !== '');

        if ($isDateFilter) {
            $dateVendors = $this->findSelectedVendorId($rfq, $venFromDate, $venToDate, $searchVen);
            if (!empty($dateVendors)) {
                $searchVen = array_unique(array_merge($searchVen, $dateVendors));
            }
        }

        if (!is_array($searchVen)) $searchVen = explode(',', $searchVen);

        $data = app('App\\Models\\AuctionModel')->get_rfq_details($rfq, $searchVen);
        if (empty($data)) {
            return redirect('my_order/active_rfq');
        }

        $data['uom_list']    = function_exists('getUOMNameById') ? getUOMNameById() : [];
        $data['rfq_div_cat'] = function_exists('getDivisionCategoryByCatId') ? getDivisionCategoryByCatId($data['rfq']->cat_id) : null;

        return view('user.cis-export', $data);
    }

    public function findSelectedVendorId($rfq, $venFromDate, $venToDate, $searchVen)
    {
        $q = DB::table('tbl_rfq as tr')
            ->leftJoin('tbl_rfq_price as p', function ($join) {
                $join->on('tr.rfq_record_id', '=', 'p.rfq_record_id')
                     ->where('p.is_sent', 1);
            })
            ->where('tr.rfq_id', $rfq);

        if (!empty($searchVen)) $q->whereIn('tr.vend_id', $searchVen);
        if (!empty($venFromDate)) {
            $from = function_exists('changeCustomDateFormate') ? changeCustomDateFormate($venFromDate) : date('Y-m-d', strtotime($venFromDate));
            $q->where('p.created_date', '>=', $from.' 00:00:00');
        }
        if (!empty($venToDate)) {
            $to = function_exists('changeCustomDateFormate') ? changeCustomDateFormate($venToDate) : date('Y-m-d', strtotime($venToDate));
            $q->where('p.created_date', '<=', $to.' 23:59:59');
        }

        $vendorIds = $q->pluck('tr.vend_id')->toArray();
        return $vendorIds;
    }

    # -------------------------------------------------------------------------
    # FORCE STOP (force_stop)
    # -------------------------------------------------------------------------
    public function forceStop(Request $request)
    {
        $rfqNo = $request->post('rfq_no');
        if (!$rfqNo) {
            return response()->json(['status'=>'error','message'=>'Invalid auction ID.']);
        }

        $currentTime = date('H:i:s');

        DB::table('rfq_auctions')
            ->where('rfq_no', $rfqNo)
            ->update([
                'is_forcestop'     => '1',
                'auction_end_time' => $currentTime
            ]);

        if (DB::affectingStatement('SELECT ROW_COUNT()')) {
            return response()->json(['status'=>'success','message'=>'Auction forcibly stopped successfully.']);
        }
        return response()->json(['status'=>'error','message'=>'Failed to stop auction or auction already stopped.']);
    }

    # -------------------------------------------------------------------------
    # GET BOOKED TIMES (get_booked_times)
    # -------------------------------------------------------------------------
    public function getBookedTimes(Request $request)
    {
        $rawDate = $request->post('auction_date'); // d/m/Y
        $scheduleDate = date('Y-m-d', strtotime(str_replace('/', '-', $rawDate)));

        $booked = DB::table('rfq_auctions')
            ->where('auction_date', $scheduleDate)
            ->pluck('auction_start_time')
            ->toArray();

        return response()->json($booked);
    }
}
