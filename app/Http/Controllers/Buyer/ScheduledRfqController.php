<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\Product;
use App\Models\Category;
use App\Models\Division;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ScheduledRfqController extends Controller
{
    public function index(Request $request) {

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        // DB::enableQueryLog();
        $query = Rfq::select('rfqs.*')
                    ->where('rfqs.buyer_id', getParentUserId())
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
        if($request->filled('product_name')){
            $query->whereHas('rfqProducts.masterProduct', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->product_name . '%');
            });
        }
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

            $query->whereBetween('scheduled_date', [$from_date, $to_date]);
        } else {
            if ($request->filled('from_date')) {
                $from_date = Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay()->format('Y-m-d H:i:s');
                $query->where('scheduled_date', '>=', $from_date);
            }
            if ($request->filled('to_date')) {
                $to_date = Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay()->format('Y-m-d H:i:s');
                $query->where('scheduled_date', '<=', $to_date);
            }
        }

        $query->orderBy('updated_at', 'DESC');
        $query->where('record_type', 2);
        $query->where('buyer_rfq_status', '2');

        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('buyer.rfq.scheduled.partials.table', compact('results'))->render();
        }
        return view('buyer.rfq.scheduled.index', compact('results'));
    }

    public function delete(Request $request) {

        $rfq_id=$request->rfq_id;
        $rfq = Rfq::with(['rfqProducts.productVariants', 'rfqVendors'])->where('rfq_id', $rfq_id)->where('record_type', 2)->where('buyer_id', getParentUserId())->where('buyer_rfq_status', 2)->first();
        if (!$rfq) {
            return response()->json(['status' => 'failed','message' => 'RFQ not found.'], 200);
        }

        // Check if scheduled_date is > today
        $today = Carbon::today(); // today’s date without time
        $scheduledDate = Carbon::parse($rfq->scheduled_date)->startOfDay();

        if ($scheduledDate->lte($today)) {
            return response()->json(['status' => 'failed','message' => 'RFQ cannot be deleted. Scheduled date is today or in the past.'],  200);
        }

        DB::beginTransaction();

        try {
            // Delete productVariants
            foreach ($rfq->rfqProducts as $product) {
                $product->productVariants()->delete();
            }

            // Delete rfqProducts
            $rfq->rfqProducts()->delete();

            // Delete rfqVendors
            $rfq->rfqVendors()->delete();

            // Delete RFQ
            $rfq->delete();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'RFQ and all related data deleted successfully.','url'=>route('buyer.rfq.scheduled-rfq')], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'failed','message' => 'Error deleting RFQ. '.$e->getMessage()], 200);
        }
    }
}
