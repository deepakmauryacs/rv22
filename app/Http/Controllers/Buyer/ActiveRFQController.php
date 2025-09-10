<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Division;
use App\Models\LiveVendorProduct;
use App\Models\Rfq;
use App\Models\RfqProduct;
use Carbon\Carbon;
use DB;

class ActiveRFQController extends Controller
{
    public function index(Request $request)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();

        // DB::enableQueryLog();
        $query = Rfq::select('rfqs.*')
                    ->where('rfqs.buyer_id', getParentUserId())
                    ->whereNotIn('rfqs.buyer_rfq_status', [2, 5, 8, 10])
                    // Apply filter only if any of the inputs are present
                    ->when(
                        $request->filled('division') || $request->filled('category') || $request->filled('product_name'),
                        function ($query1) use ($request) {
                            $query1->whereHas('rfqProducts.masterProduct', function ($q) use ($request) {
                                if ($request->filled('division') && !empty($request->division)) {
                                    $q->where('division_id', $request->division);
                                }
                                if ($request->filled('category') && !empty($request->category)) {
                                    $categories = explode(",", $request->category);
                                    $q->whereIn('category_id', $categories);
                                }
                                if ($request->filled('product_name')) {
                                    $q->where('product_name', 'like', '%' . $request->product_name . '%');
                                }
                            });
                        }
                    )
                    ->with([
                        'rfqVendorQuotations' => function ($q) {
                            $q->where('status', 1);
                        },
                        'rfqProducts.masterProduct',
                        'buyerUser',
                        'buyerBranch' => function ($q) {
                            $q->where('user_type', 1);
                        },
                        'rfq_auction'=> function ($query) {
                            $query->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time');
                        }
                    ])
                    ->addSelect([
                        'rfq_response_received' => function ($q) {
                            $q->selectRaw('COUNT(DISTINCT vendor_id)')
                                ->from('rfq_vendor_quotations')
                                ->whereColumn('rfq_id', 'rfqs.rfq_id')
                                ->where('status', 1);
                        }
                    ]);

        if (!empty($user_branch_id_only)) {
            $query->whereIn('buyer_branch', $user_branch_id_only);
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', 'like', '%' .$request->rfq_no . '%');
        }
        if ($request->filled('rfq_status') && !empty($request->rfq_status)){
            if($request->rfq_status == 'auction-completed'){
                $query->whereHas('rfq_auction', function ($q) {
                    $q->where('auction_date', '<=', Carbon::now()->format('Y-m-d'));
                    $q->where('auction_end_time', '>=', Carbon::now()->format('H:i:s'));
                });
            }else{
                $query->where('buyer_rfq_status', $request->rfq_status);
            }
        }
        if ($request->filled('prn_number')){
            $query->where('prn_no', "like", '%'.$request->prn_number.'%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');

            $query->whereBetween('created_at', [$from_date, $to_date]);
        } else {
            if ($request->filled('from_date')) {
                $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '>=', $from_date);
            }

            if ($request->filled('to_date')) {
                $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '<=', $to_date);
            }
        }

        $query->orderBy('updated_at', 'DESC');
        $query->where('record_type', 2);

        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('buyer.rfq.active-rfq.partials.table', compact('results'))->render();
        }

        $divisions = Division::where("status", 1)->orderBy('division_name', 'asc')->get();
        $categories = Category::where("status", 1)->get();

        $unique_category = [];
        foreach ($categories as $category) {
            $name = $category->category_name;
            $id = $category->id;
            if (!isset($unique_category[$name])) {
                $unique_category[$name] = [];
            }
            $unique_category[$name][] = $id;
        }
        ksort($unique_category);

        return view('buyer.rfq.active-rfq.index', compact('divisions', 'unique_category', 'results'));
    }

    public function rfq_details(Request $request, $rfq_id)
    {
        $company_id = getParentUserId();
        $buyer_branch = DB::table('branch_details')
            ->select('id', 'branch_id', 'name')
            ->where("user_id", $company_id)
            ->where('user_type', 1)
            ->where('record_type', 1)
            ->where('status', 1)
            ->get();
        $rfq = Rfq::with([
                        'rfqProducts',
                        'rfqProducts.masterProduct:id,division_id,category_id,product_name',
                        'rfqProducts.masterProduct.division:id,division_name',
                        'rfqProducts.masterProduct.category:id,category_name',
                        'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                            $q->where('rfq_id', $rfq_id);
                        },
                        'rfqVendors'=> function ($q){
                            $q->selectRaw('MAX(vendor_user_id) as vendor_user_id, MAX(rfq_id) as rfq_id');
                            $q->groupBy('vendor_user_id');
                        },
                    ])
                    ->where('rfq_id', $rfq_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 2)
                    ->first();
        if(empty($rfq)){
            session()->flash('error', "Draft RFQ not found");
            return redirect()->to(route('buyer.dashboard'));
        }

        $rfq_vendors = $rfq->rfqVendors;
        return view('buyer.rfq.rfq-details', compact('rfq','rfq_vendors', 'buyer_branch'));
    }
    private function extractRFQVendors($rfqVendors){
        return collect($rfqVendors)
            ->filter() // removes nulls or falsy entries
            ->pluck('vendor_user_id')
            ->values()
            ->all();
    }
    public function sent_rfq(Request $request)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();
        // DB::enableQueryLog();
        $query = Rfq::select('rfqs.*')
                    ->where('rfqs.buyer_id', getParentUserId())
                    // Apply filter only if any of the inputs are present
                    ->when(
                        $request->filled('division') || $request->filled('category') || $request->filled('product_name'),
                        function ($query1) use ($request) {
                            $query1->whereHas('rfqProducts.masterProduct', function ($q) use ($request) {
                                if ($request->filled('division') && !empty($request->division)) {
                                    $q->where('division_id', $request->division);
                                }
                                if ($request->filled('category') && !empty($request->category)) {
                                    $categories = explode(",", $request->category);
                                    $q->whereIn('category_id', $categories);
                                }
                                if ($request->filled('product_name')) {
                                    $q->where('product_name', 'like', '%' . $request->product_name . '%');
                                }
                            });
                        }
                    )
                    ->with([
                        'rfqVendorQuotations' => function ($q) {
                            $q->where('status', 1);
                        },
                        'rfqProducts.masterProduct',
                        'buyerUser',
                        'buyerBranch' => function ($q) {
                            $q->where('user_type', 1);
                        },
                        'rfq_auction'=> function ($query) {
                            $query->select('rfq_no', 'auction_date', 'auction_start_time', 'auction_end_time');
                        }
                    ])
                    ->addSelect([
                        'rfq_response_received' => function ($q) {
                            $q->selectRaw('COUNT(DISTINCT vendor_id)')
                                ->from('rfq_vendor_quotations')
                                ->whereColumn('rfq_id', 'rfqs.rfq_id')
                                ->where('status', 1);
                        }
                    ]);

        if (!empty($user_branch_id_only)) {
            $query->whereIn('buyer_branch', $user_branch_id_only);
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', 'like', '%' .$request->rfq_no . '%');
        }
        if ($request->filled('rfq_status') && !empty($request->rfq_status)){
            $query->where('buyer_rfq_status', $request->rfq_status);
        }
        if ($request->filled('prn_number')){
            $query->where('prn_no', "like", '%'.$request->prn_number.'%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
            $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');

            $query->whereBetween('created_at', [$from_date, $to_date]);
        } else {
            if ($request->filled('from_date')) {
                $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '>=', $from_date);
            }

            if ($request->filled('to_date')) {
                $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
                $query->where('created_at', '<=', $to_date);
            }
        }

        $query->orderBy('updated_at', 'DESC');
        $query->where('record_type', 2);

        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        // dd(DB::getQueryLog());

        if ($request->ajax()) {
            return view('buyer.rfq.sent-rfq.partials.table', compact('results'))->render();
        }

        $divisions = Division::where("status", 1)->orderBy('division_name', 'asc')->get();
        $categories = Category::where("status", 1)->get();

        $unique_category = [];
        foreach ($categories as $category) {
            $name = $category->category_name;
            $id = $category->id;
            if (!isset($unique_category[$name])) {
                $unique_category[$name] = [];
            }
            $unique_category[$name][] = $id;
        }
        ksort($unique_category);

        return view('buyer.rfq.sent-rfq.index', compact('divisions', 'unique_category', 'results'));
    }

    public function closeRFQ(Request $request)
    {
        $rfq_id = $request->rfq_id;
        if(empty($rfq_id)){
            return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again later!']);
        }
        $buyer_id = getParentUserId();
        $rfq = Rfq::where('rfq_id', $rfq_id)->where('buyer_id', $buyer_id)->where('record_type', 2)
                                ->whereNotIn('buyer_rfq_status', [5, 8, 10])->first();
        if(empty($rfq)){
            return response()->json(['status' => false, 'message' => 'RFQ not found, Please try again later!']);
        }

        $buyer_rfq_status = 8;
        if($rfq->buyer_rfq_status==9){
            $buyer_rfq_status = 10;
        }

        DB::beginTransaction();

        try {

            $rfq->buyer_rfq_status = $buyer_rfq_status;
            $rfq->save();

            $evaluated_vendors = $this->evaluateRFQVendorsStatusForCloseRFQ($rfq->rfq_id);
            if(!empty($evaluated_vendors['update_vendor_rfq_status_wise'])){
                foreach ($evaluated_vendors['update_vendor_rfq_status_wise'] as $vend_rfq_status => $vendor_ids) {
                    DB::table("rfq_vendors")
                        ->where('rfq_id', $rfq->rfq_id)
                        ->whereIn('vendor_user_id', array_values($vendor_ids))
                        ->update(['vendor_status' => $vend_rfq_status]);
                }
            }

            if(!empty($evaluated_vendors['all_vendors'])){
                $all_vendors = array_keys($evaluated_vendors['all_vendors']);
                $notification_data = array();
                $notification_data['rfq_id'] = $rfq->rfq_id;
                $notification_data['message_type'] = 'RFQ Closed';
                $notification_data['notification_link'] = route('vendor.rfq.received.index').'?frq_no='.$rfq->rfq_id;
                $notification_data['to_user_id'] = $all_vendors;
                sendNotifications($notification_data);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'RFQ Closed Successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Close RFQ, Please try again later! '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    private function evaluateRFQVendorsStatusForCloseRFQ($rfq_id){
        $rfq_vendors = DB::table('rfq_vendors')
                        ->select('vendor_user_id', 'vendor_status')
                        ->groupBy('vendor_user_id', 'vendor_status')
                        ->where('rfq_id', $rfq_id)
                        ->get()->toArray();
        //
        $status_wise_vendor = [];
        $all_vendors = [];
        foreach ($rfq_vendors as $value) {
            $vendor_rfq_status = 8; // default
            if (in_array($value->vendor_status, [1, 4, 6, 7])) {
                $vendor_rfq_status = 8;
            } elseif ($value->vendor_status == 9) {
                $vendor_rfq_status = 10;
            } elseif ($value->vendor_status == 5) {
                $vendor_rfq_status = 5;
            }

            $status_wise_vendor[$vendor_rfq_status][] = $value->vendor_user_id;
            $all_vendors[$value->vendor_user_id] = true;
        }

        unset($rfq_vendors);

        return array('update_vendor_rfq_status_wise'=>$status_wise_vendor, 'all_vendors'=>$all_vendors);
    }

    public function editRFQ(Request $request)
    {
        $rfq_id = $request->rfq_id;
        if(empty($rfq_id)){
            return response()->json(['status' => false, 'message' => 'Something went wrong, Please try again later!']);
        }
        $buyer_id = getParentUserId();
        $rfq = Rfq::where('rfq_id', $rfq_id)->where('buyer_id', $buyer_id)->where('record_type', 2)
                                ->whereNotIn('buyer_rfq_status', [5, 8, 9, 10])->first();
        if(empty($rfq)){
            return response()->json(['status' => false, 'message' => 'RFQ not found, Please try again later!']);
        }

        $is_auction = DB::table('rfq_auctions')->where('rfq_no', $rfq->rfq_id)->first();
        if(!empty($is_auction)){
            $auction_status = getAuctionStatus($is_auction->auction_date, $is_auction->auction_start_time, $is_auction->auction_end_time);
            if($auction_status!=3){
                return response()->json(['status' => false, 'message' => 'RFQ Auction is scheduled or in progress, Please try again later!']);
            }
        }
        unset($is_auction);

        $rfq_data = Rfq::with(['rfqProducts', 'rfqProductVariants', 'rfqVendors'])
                        ->where('rfq_id', $rfq->rfq_id)
                        ->where('buyer_id', $buyer_id)
                        ->where('record_type', 2)
                        ->whereNotIn('buyer_rfq_status', [5, 8, 9, 10])
                        ->first()->toArray();
        DB::beginTransaction();

        try {

            $rfq_id = makeDuplicateRFQData($rfq_data, 3, 'edit');

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'RFQ Edit Successfully',
                'redirect_url' => route('buyer.rfq.compose-draft-rfq', $rfq_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Edit RFQ, Please try again later! '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }
}
