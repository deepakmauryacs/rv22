<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForwardAuction;
use App\Models\ForwardAuctionProduct;
use App\Models\ForwardAuctionVendor;
use App\Models\Buyer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ForwardAuctionController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Kolkata');
    }

    public function index(Request $request)
    {
        // Subquery: Get concatenated product names per auction
        $subQueryProducts = DB::table('forward_auction_products')
            ->select('auction_id', DB::raw('GROUP_CONCAT(product_name SEPARATOR ", ") as product_names'))
            ->groupBy('auction_id');

        // Join to branch_details using buyer_branch (branch_id)
        $subQueryBranch = DB::table('branch_details as bd1')
            ->select('bd1.branch_id', 'bd1.name')
            ->join(DB::raw('
                (SELECT MIN(id) as min_id FROM branch_details
                 WHERE user_type = 1 AND record_type = 1 AND status = 1
                 GROUP BY branch_id) as bd2
            '), 'bd1.id', '=', 'bd2.min_id');

        $query = DB::table('forward_auctions as fa')
            ->select(
                'fa.*',
                'sub_p.product_names',
                'bu.name as buyer_user_name',
                'sub_b.name as branch_name'
            )
            ->leftJoinSub($subQueryProducts, 'sub_p', 'fa.auction_id', '=', 'sub_p.auction_id')
            ->leftJoin('users as bu', 'fa.buyer_user_id', '=', 'bu.id')
            ->leftJoinSub($subQueryBranch, 'sub_b', 'fa.buyer_branch', '=', 'sub_b.branch_id')
            ->where('fa.buyer_id', getParentUserId());

        // Filters
        if ($request->filled('auction_no')) {
            $query->where('fa.auction_id', 'like', '%' . $request->auction_no . '%');
        }

        if ($request->filled('product_name')) {
            $query->where('sub_p.product_names', 'like', '%' . $request->product_name . '%');
        }

        if ($request->filled('auction_date')) {
            $dateParts = explode('/', $request->auction_date);
            if (count($dateParts) === 3) {
                $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; // Y-m-d
                $query->whereDate('fa.schedule_date', '=', $formattedDate);
            }
        }

        $query->orderBy('fa.created_at', 'desc');
        $perPage = $request->input('per_page', 10);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('buyer.forward-auction.partials.table', compact('results'))->render();
        }

        return view('buyer.forward-auction.index', compact('results'));
    }

    public function view($auction_id)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly(); // define as helper or via Auth

        // 1. Get Auction Info
        $auction = DB::table('forward_auctions as auction')
            ->join('branch_details as branch', 'branch.branch_id', '=', 'auction.buyer_branch')
            ->select('auction.*', 'branch.name as branch_name')
            ->where('auction.auction_id', $auction_id)
            ->where('auction.buyer_id',  getParentUserId())
            ->when(!empty($user_branch_id_only), function ($q) use ($user_branch_id_only) {
                return $q->whereIn('branch.id', $user_branch_id_only);
            })
            ->first();

        if (!$auction) {
            return redirect()->route('forward_auction.index')->with('invalid_url', 'Invalid URL');
        }

        // 2. Get Products
        $products = DB::table('forward_auction_products as product')
            ->leftJoin('uoms as uom', 'uom.id', '=', 'product.uom')
            ->where('auction_id', $auction_id)
            ->select('product.*', 'uom.uom_name')
            ->get();

        // 3. Vendors
        $vendors = DB::table('forward_auction_vendors as fav')
            ->join('users as u', 'u.id', '=', 'fav.vendor_id')
            ->leftJoin('vendors as v', 'v.user_id', '=', 'u.id')
            ->where('fav.auction_id', $auction_id)
            ->select('fav.vendor_id', 'v.legal_name', 'u.mobile', 'u.country_code')
            ->get();


        // 4. Replies
        $replies = DB::table('forward_auction_replies')
            ->where('auction_id', $auction_id)
            ->get();

        // Organize replies by vendor_id + product_id
        $replyMap = [];
        foreach ($replies as $reply) {
            $replyMap[$reply->vendor_id][$reply->auction_product_id] = $reply->price;
        }

        // 5. Build Final Vendor Bid Array (Even if no bid submitted)
        $vendorBids = [];
        foreach ($vendors as $vendor) {
            $vendor_id = $vendor->vendor_id;
            $vendorBids[$vendor_id] = [
                'name' => $vendor->legal_name ?? 'Unknown',
                'mobile' => $vendor->mobile ?? '',
                'country_code' => $vendor->country_code ?? '',
                'prices' => [],
                'total' => 0,
            ];

            $total = 0;
            foreach ($products as $product) {
                $price = $replyMap[$vendor_id][$product->id] ?? null;
                $vendorBids[$vendor_id]['prices'][$product->id] = $price;
                if (!is_null($price)) {
                    $total += $price * $product->quantity;
                }
            }
            $vendorBids[$vendor_id]['total'] = $total;
        }

        // 6. Sort vendorBids by total price DESC (highest total = rank 1)
        uasort($vendorBids, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // 7. Add rank field
        $rank = 1;
        foreach ($vendorBids as &$bid) {
            $bid['rank'] = $rank++;
        }
        unset($bid);



        // 8. Pass to View
        return view('buyer.forward-auction.view', [
            'page_title' => 'Forward Auction',
            'auction' => $auction,
            'products' => $products,
            'vendorBids' => $vendorBids,
        ]);
    }
    public function create()
    {
        $buyer_id = getParentUserId();
        $branches =  $this->getBuyerBranches($buyer_id);
        $uoms = DB::table('uoms')->get();
        // Fetch only active currencies (status = '1')
        $currencies = DB::table('currencies')
        ->where('status', '1')
        ->get();
        return view('buyer.forward-auction.create', compact('branches', 'uoms','currencies'));
    }

    private function getBuyerBranches($buyer_id)
    {
        return DB::table('branch_details as bd')
            ->join('users as u', 'bd.user_id', '=', 'u.id')
            ->join('countries as c', 'bd.country', '=', 'c.id')
            ->join('states as s', 'bd.state', '=', 's.id')
            ->where('bd.user_type', 1)              // Buyer user type
            ->where('bd.user_id', $buyer_id)        // Filter for given buyer
            ->where('bd.status', '1')                // Active status in branch_details
            ->where('u.is_profile_verified', '1')   // Only users with verified profiles
            ->select(
                'bd.id',
                'bd.branch_id',
                'bd.name as branch_name',
                'bd.address',
                'c.name as country_name',
                's.name as state_name',
                'bd.city',
                'bd.pincode',
                'bd.mobile',
                'bd.email'
            )
            ->get();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_date'       => 'required',
            'schedule_start_time' => 'required',
            'buyer_branch'        => 'required|integer',
            'vendor_id'           => 'required|array',
            'product_name'        => 'required|array|min:1',
            'product_name.*'      => 'required|string|max:500',
            'quantity.*'          => 'required|numeric|min:1',
            'start_price.*'       => 'required|numeric|min:0',
            'uom.*'               => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>'error', 'errors'=>$validator->errors()->all()]);
        }

        // Date/time basic validation
        $schedule_date = Carbon::createFromFormat('d/m/Y', $request->schedule_date)->toDateString();
        $schedule_start_time = $request->schedule_start_time; // e.g. "01:00:00"
        $current_date = Carbon::now()->toDateString();
        $current_time = Carbon::now()->format('H:i:s');

        if ($schedule_date < $current_date) {
            return response()->json(['status'=>'error', 'errors'=>['Schedule date cannot be in the past.']]);
        }

        if ($schedule_date == $current_date) {
            // Combine today's date and schedule_start_time, and compare with now
            $scheduledDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_date . ' ' . $schedule_start_time);
            $now = Carbon::now();

            if ($scheduledDateTime->lessThan($now)) {
                return response()->json(['status'=>'error', 'errors'=>['Schedule time cannot be in the past for today.']]);
            }
        }


        DB::beginTransaction();

        try {
            $user = auth()->user();
            $buyer_id = getParentUserId();
            $schedule_end_time = Carbon::parse($schedule_start_time)->addMinutes(30)->format('H:i:s');

            // 1. Generate auction_id
            $buyerDetail = Buyer::where('user_id', $buyer_id)->first();
            $shortCode = strtoupper($buyerDetail->buyer_code ?? 'XX');
            $seq = DB::table('forward_auction_id_logs')->where('user_id', $buyer_id)->count() + 1;
            $auction_id = 'F' . $shortCode . str_pad($seq, 5, '0', STR_PAD_LEFT);
            DB::table('forward_auction_id_logs')->insert(['auction_id' => $auction_id, 'user_id' => $buyer_id]);

            // 2. Insert ForwardAuction record
            $auction = ForwardAuction::create([
                'auction_id'        => $auction_id,
                'buyer_id'          => $buyer_id,
                'buyer_user_id'     => $user->id,
                'schedule_date'     => $schedule_date,
                'schedule_start_time'=> $schedule_start_time,
                'schedule_end_time' => $schedule_end_time,
                'buyer_branch'      => $request->buyer_branch,
                'branch_address'    => $request->branch_address,
                'remarks'           => $request->remarks,
                'price_basis'       => $request->price_basis,
                'payment_terms'     => $request->payment_terms,
                'delivery_period'   => $request->delivery_period,
                'currency'          => $request->currency,
            ]);

            // 3. Products & file uploads
            $productIds = [];
            foreach ($request->product_name as $i => $productName) {
                $uploadedFileName = null;
                if ($request->hasFile("file_attachment.$i")) {
                    $uploadedFile = $request->file("file_attachment.$i");
                    $uploadedFileName = $uploadedFile->store('auction_files', 'public');
                }

                $product = ForwardAuctionProduct::create([
                    'auction_id'              => $auction->auction_id,
                    'product_name'            => trim($productName),
                    'specs'                   => $request->specs[$i] ?? null,
                    'quantity'                => $request->quantity[$i],
                    'uom'                     => $request->uom[$i],
                    'start_price'             => $request->start_price[$i],
                    'min_bid_increment_amount'=> $request->min_bid_increment_amount[$i] ?? null,
                    'file_attachment'         => $uploadedFileName,
                ]);
                $productIds[] = $product->id;
            }

            // 4. Vendor associations
            foreach ($request->vendor_id as $vendorId) {
                foreach ($productIds as $prodId) {
                    ForwardAuctionVendor::create([
                        'auction_id'          => $auction->auction_id,
                        'auction_product_id'  => $prodId,
                        'vendor_id'           => $vendorId,
                    ]);
                }
            }

            // 5. (Optional) Notification (implement using Laravel's Notification system or Events)
            // event(new ForwardAuctionCreated(...));

            DB::commit();
            return response()->json(['status'=>'success', 'message'=>'Auction and products saved successfully', 'auction_id' => $auction->auction_id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>'error', 'message'=>'Failed to save auction: '.$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        // Get auction with products only
        $auction = ForwardAuction::with(['products'])->findOrFail($id);

        $buyer_id = getParentUserId();
        $branches = $this->getBuyerBranches($buyer_id);
        $uoms = DB::table('uoms')->get();
        $currencies = DB::table('currencies')->where('status', '1')->get();

        // Get selected vendors (with details) from forward_auction_vendors joined with vendors table
        $selectedVendors = DB::table('forward_auction_vendors as fav')
            ->join('vendors as v', 'fav.vendor_id', '=', 'v.user_id')
            ->where('fav.auction_id', $auction->auction_id)
            ->select('v.user_id as vendor_id', 'v.legal_name  as vendor_name') // adjust columns as needed
            ->distinct()
            ->get();

        // echo "<pre>";
        // print_r($uoms); die();



        return view('buyer.forward-auction.edit', compact(
            'auction',
            'branches',
            'uoms',
            'currencies',
            'selectedVendors'
        ));
    }

    public function update(Request $request, $id)
    {
        $auction = ForwardAuction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'schedule_date' => 'required',
            'schedule_start_time' => 'required',
            'buyer_branch' => 'required',
            'branch_address' => 'required',
            'currency' => 'required',
            'vendor_id' => 'required|array|min:1',
            'product_name' => 'required|array|min:1',
            'product_name.*' => 'required|string|max:500',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:0.01',
            'uom' => 'required|array|min:1',
            'uom.*' => 'required|exists:uoms,id',
            'start_price' => 'required|array|min:1',
            'start_price.*' => 'required|numeric|min:0.01',
            'min_bid_increment_amount' => 'required|array|min:1',
            'min_bid_increment_amount.*' => 'required|numeric|min:0.01',
            'file_attachment.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,csv|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()->all()]);
        }

        DB::beginTransaction();
        try {
            $scheduleDateTime = Carbon::createFromFormat('d/m/Y', $request->schedule_date);

            $auction->update([
                'schedule_date' => $scheduleDateTime->format('Y-m-d'),
                'schedule_start_time' => $request->schedule_start_time,
                'schedule_end_time' => Carbon::parse($request->schedule_start_time)->addHours(1)->format('H:i:s'),
                'buyer_branch' => $request->buyer_branch,
                'branch_address' => $request->branch_address,
                'remarks' => $request->remarks,
                'price_basis' => $request->price_basis,
                'payment_terms' => $request->payment_terms,
                'delivery_period' => $request->delivery_period,
                'currency' => $request->currency,
            ]);

            $productIds = [];

            foreach ($request->product_name as $index => $productName) {
                $product = null;
                if (isset($request->product_id[$index])) {
                    $product = ForwardAuctionProduct::find($request->product_id[$index]);
                }


                $productData = [
                    'product_name' => $productName,
                    'specs' => $request->specs[$index] ?? null,
                    'quantity' => $request->quantity[$index],
                    'uom' => $request->uom[$index],
                    'start_price' => $request->start_price[$index],
                    'min_bid_increment_amount' => $request->min_bid_increment_amount[$index],
                    'auction_id' => $auction->auction_id,
                ];

                // Handle file upload
                if ($request->hasFile('file_attachment') && isset($request->file('file_attachment')[$index])) {
                    $file = $request->file('file_attachment')[$index];

                    if ($file && $file->isValid()) {
                        // Delete old file if it exists
                        if ($product && !empty($product->file_attachment) && File::exists(public_path('Uploads/forward_auction_files/' . $product->file_attachment))) {
                            File::delete(public_path('Uploads/forward_auction_files/' . $product->file_attachment));
                        }

                        $extension = $file->getClientOriginalExtension();
                        $filename = strtolower(time() . '-' . str_replace(['_', ' ', '%20'], ['-', '', ''], pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))) . '.' . $extension;
                        $filename = str_replace(' ', '', $filename);

                        $uploadDir = public_path('Uploads/forward_auction_files');
                        File::ensureDirectoryExists($uploadDir);

                        $file->move($uploadDir, $filename);
                        $productData['file_attachment'] = $filename;
                    }
                } elseif ($product) {
                    // Keep the existing file if no new one is uploaded
                    $productData['file_attachment'] = $product->file_attachment;
                }

                if ($product) {
                    $product->update($productData);
                    $productIds[] = $product->id;
                } else {
                    $newProduct = ForwardAuctionProduct::create($productData);
                    $productIds[] = $newProduct->id;
                }
            }

            // Delete removed products
            ForwardAuctionProduct::where('auction_id', $auction->auction_id)
                ->whereNotIn('id', $productIds)
                ->delete();

            // Update vendors
            $vendorIds = $request->vendor_id;

            // Delete vendors not in selected list
            ForwardAuctionVendor::where('auction_id', $auction->auction_id)
                ->whereNotIn('vendor_id', $vendorIds)
                ->delete();

            // Sync vendors with products
            foreach ($vendorIds as $vendorId) {
                foreach ($productIds as $productId) {
                    ForwardAuctionVendor::updateOrCreate(
                        [
                            'auction_id' => $auction->auction_id,
                            'auction_product_id' => $productId,
                            'vendor_id' => $vendorId
                        ],
                        ['vendor_id' => $vendorId]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Auction updated successfully!',
                'auction_id' => $auction->auction_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating auction: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $auction_id = $request->auction_id ?? $request->route('auction_id');
        $auction = ForwardAuction::where('auction_id', $auction_id)->firstOrFail();

        $auctionStart = Carbon::parse($auction->schedule_date.' '.$auction->schedule_start_time);
        if ($auctionStart->isPast()) {
            return response()->json(['status'=>'error', 'message'=>'Cannot delete - auction has already started or completed']);
        }

        DB::beginTransaction();
        try {
            // Delete product files
            $products = ForwardAuctionProduct::where('auction_id', $auction->auction_id)->get();
            foreach ($products as $product) {
                if (!empty($product->file_attachment)) {
                    Storage::disk('public')->delete($product->file_attachment);
                }
            }

            // Delete relations
            ForwardAuctionVendor::where('auction_id', $auction->auction_id)->delete();
            ForwardAuctionProduct::where('auction_id', $auction->auction_id)->delete();
            $auction->delete();

            // (Optional) Notification...

            DB::commit();
            return response()->json(['status'=>'success', 'message'=>'Auction deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status'=>'error', 'message'=>'Failed to delete auction']);
        }
    }

    public function forceStop(Request $request)
    {
        $auction_id = $request->auction_id;
        $auction = ForwardAuction::where('auction_id', $auction_id)->first();

        if (!$auction || $auction->is_forcestop == 1) {
            return response()->json(['status'=>'error', 'message'=>'Auction not found or already stopped']);
        }

        $auction->update([
            'is_forcestop' => 1,
            'schedule_end_time' => now()->format('H:i:s'),
            'updated_at' => now(),
        ]);
        return response()->json(['status'=>'success', 'message'=>'Auction has ended.']);
    }

    public function getBookedTimes(Request $request)
    {
        $raw_date = $request->schedule_date ?? $request->input('schedule_date');
        $schedule_date = Carbon::parse(str_replace('/', '-', $raw_date))->toDateString();

        $booked = ForwardAuction::where('schedule_date', $schedule_date)
            ->pluck('schedule_start_time')->toArray();
        return response()->json($booked);
    }

    public function getProductSuggestions(Request $request)
    {

        $keyword = $request->get('term');
        $buyer_id = getParentUserId();

        $products = DB::table('forward_auction_products as fap')
            ->select('fap.product_name')
            ->join('forward_auctions as fa', 'fap.auction_id', '=', 'fa.auction_id')
            ->where('fa.buyer_id', $buyer_id)
            ->distinct()
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('fap.product_name', 'like', '%' . $keyword . '%');
            })
            ->limit(10)
            ->pluck('fap.product_name');

        return response()->json($products);
    }

    public function searchVendors(Request $request)
    {
        $q = trim($request->post('q'));
        $page = (int) $request->post('page', 1);

        if (mb_strlen($q) < 4) {
            return response()->json(['status' => true, 'data' => [], 'has_more' => false]);
        }

        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Split search string into words
        $words = preg_split('/\s+/', $q);

        $resultQuery = DB::table('users')
            ->join('vendors', 'vendors.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.mobile',
                'users.country_code',
                'vendors.legal_name',
                'vendors.vendor_code'
            )
            ->where('users.user_type', '2') // 2 = Vendor
            ->where('users.status', '1');   // Active users

        // Each word must be in legal_name (AND logic)
        foreach ($words as $word) {
            $resultQuery->where('vendors.legal_name', 'LIKE', "%{$word}%");
        }

        $result = $resultQuery
            ->orderBy('vendors.legal_name')
            ->offset($offset)
            ->limit($limit + 1) // +1 to check for more pages
            ->get();

        $has_more = $result->count() > $limit;

        $data = $result->slice(0, $limit)->map(function ($user) {
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'mobile'       => $user->mobile,
                'country_code' => $user->country_code ?? '',
                'legal_name'   => $user->legal_name ?? '',
                'vendor_code'  => $user->vendor_code ?? '',
            ];
        })->values();

        return response()->json([
            'status'   => true,
            'data'     => $data,
            'has_more' => $has_more
        ]);
    }

    public function exportCIS($auction_id)
    {
        $data['page_title'] = "Export CIS";

        // 1. Auction Info
        $auction = DB::table('forward_auctions as auction')
            ->leftJoin('branch_details as branch', 'branch.branch_id', '=', 'auction.buyer_branch')
            ->select('auction.*', 'branch.name as branch_name')
            ->where('auction.auction_id', $auction_id)
            ->first();
        $data['auction'] = $auction;

        // 2. Auction Products
        $products = DB::table('forward_auction_products as product')
            ->leftJoin('uoms as uom', 'uom.id', '=', 'product.uom')
            ->select('product.*', 'uom.uom_name')
            ->where('product.auction_id', $auction_id)
            ->get();
        $data['products'] = $products;

        // 3. Vendors
        $vendors = DB::table('forward_auction_vendors as fav')
            ->leftJoin('users as u', 'u.id', '=', 'fav.vendor_id')
            ->leftJoin('vendors as v', 'v.user_id', '=', 'u.id')
            ->select('fav.vendor_id', 'v.legal_name', 'u.mobile', 'u.country_code')
            ->where('fav.auction_id', $auction_id)
            ->get();

        // 4. All Replies
        $replies = DB::table('forward_auction_replies')
            ->where('auction_id', $auction_id)
            ->get();

        // 5. Organize replies by [vendor_id][product_id] = price
        $replyMap = [];
        foreach ($replies as $reply) {
            $replyMap[$reply->vendor_id][$reply->auction_product_id] = $reply->price;
        }

        // 6. Build Vendor Bids Array
        $vendorBids = [];
        foreach ($vendors as $vendor) {
            $vendor_id = $vendor->vendor_id;
            $vendorBids[$vendor_id] = [
                'name' => $vendor->legal_name ?? 'Unknown',
                'mobile' => $vendor->mobile ?? '',
                'country_code' => $vendor->country_code ?? '',
                'prices' => [],
                'total' => 0,
            ];

            $total = 0;
            foreach ($products as $product) {
                $price = $replyMap[$vendor_id][$product->id] ?? null;
                $vendorBids[$vendor_id]['prices'][$product->id] = $price;
                if (!is_null($price)) {
                    $total += $price * $product->quantity;
                }
            }
            $vendorBids[$vendor_id]['total'] = $total;
        }

        // 7. Sort by total price descending (highest total = rank 1)
        uasort($vendorBids, function ($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        // 8. Add Rank
        $rank = 1;
        foreach ($vendorBids as &$bid) {
            $bid['rank'] = $rank++;
        }
        unset($bid);

        $data['vendorBids'] = $vendorBids;

        // 9. Load export view (Blade file)
        return view('buyer.forward-auction.export-cis-sheet-excel', $data);
    }



}
