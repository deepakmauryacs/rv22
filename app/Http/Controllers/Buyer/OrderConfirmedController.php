<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Division;
use App\Models\Category;
use App\Models\Rfq;
use DB;
class OrderConfirmedController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('order_variants','vendor','rfq')->where('buyer_id', getParentUserId())->where('order_status','!=','3');
        if ($request->filled('order_no')){
            $query->where('po_number', 'like', '%' . $request->order_no . '%');
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', 'like', '%' . $request->rfq_no . '%');
        }
        if($request->filled('division')){
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('division_id', $request->division);
                });
            });
        }
        if($request->filled('category')){
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            });
        }
        if($request->filled('branch')){
            $query->whereHas('rfq', function ($q) use ($request) {
                $q->where('buyer_branch', $request->branch);
            });
        }
        if($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }
        if($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if(!$request->filled('from_date') && $request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if($request->filled('product_name')){
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('product_name', 'like', "%$request->product_name%");
                });
            });
        }
        if ($request->filled('vendor_name'))
        {
            $legal_name=$request->vendor_name;
            $query->whereHas('vendor', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('status')){
            $query->where('order_status', $request->status);
        }
        $order=$request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('buyer.rfq.order_confirmed.partials.table', compact('results'))->render();
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
        $branchs=getBuyerBranchs();

        return view('buyer.rfq.order_confirmed.index',compact('results','divisions','unique_category','branchs'));
    }

    public function view(Request $request,$id) {
        $buyer_id = getParentUserId();
        $order = Order::with([
                    // 'rfq',
                    'buyer',
                    'order_variants.frq_variant',
                    'order_variants.frq_quotation_variant'=>function($q){
                        //$q->where('vendor_id', getParentUserId());
                    },
                ])
                ->where('buyer_id', $buyer_id)
                ->where('id', $id)
                ->first();
        // 
        if(empty($order)){
            session()->flash('error', "Nothing found for this order id.");
            //return to back ;
            return redirect()->back();
        }
        // echo '<pre>';
        // print_r($order);die;
        return view('buyer.rfq.order_confirmed.view', compact('order'));
    }

    public function print(Request $request,$id) {
        $order = Order::with(['vendor','vendor.user','buyer','buyer.users','rfq','rfq.buyer_branchs','order_variants.frq_variant','order_variants.frq_quotation_variant'])->where('id', $id)->first();
        // echo '<pre>';
        // print_r($order);die;
        return view('buyer.rfq.order_confirmed.pdf',compact('order'));
    }
    public function cancel(Request $request, $id) {
        $buyer_id = getParentUserId();

        $order = Order::with(['rfq'])->where('id', $id)->where('buyer_id', $buyer_id)->first();
        if(empty($order)){
            return response()->json(['status' => false, 'message' => 'Nothing found for this order id.']);
        }
        if($order->order_status == 3){
            return response()->json(['status' => false, 'message' => 'Invalid Order.']);
        }
        if($order->order_status == 2){
            return response()->json(['status' => false, 'message' => 'Order already cancelled.']);
        }

        // check and show error: GRN Already Process, first Delete GRN!

        DB::beginTransaction();

        try {

            $order->order_status = 2;
            $order->updated_at = now();
            $order->save();

            if(!in_array($order->rfq->buyer_rfq_status, array(8, 10))){//rfq not closed manually
                $is_any_order_left = DB::table("orders")
                                    ->where('rfq_id', $order->rfq_id)
                                    ->where('buyer_id', $buyer_id)
                                    ->where('order_status', 1)
                                    ->exists();
                if(!$is_any_order_left){
                    $order->rfq->buyer_rfq_status = 7;
                }else{
                    if($order->rfq->buyer_rfq_status==5){
                        $order->rfq->buyer_rfq_status = 9;
                    }
                }
                $order->rfq->save();
            }
            $evaluated_vendors_status = $this->reEvaluateRFQVendorsStatus($order->rfq_id);
            
            if(!empty($evaluated_vendors_status['update_vendor_rfq_status_wise'])){
                foreach ($evaluated_vendors_status['update_vendor_rfq_status_wise'] as $vend_rfq_status => $vendor_ids) {
                    DB::table("rfq_vendors")
                        ->where('rfq_id', $order->rfq_id)
                        ->whereIn('vendor_user_id', array_values($vendor_ids))
                        ->update(['vendor_status' => $vend_rfq_status]);
                }
            }

            $notification_data = array();
            $notification_data['po_number'] = $order->po_number;
            $notification_data['message_type'] = 'Order Cancelled';
            $notification_data['notification_link'] = route('vendor.rfq_order.index').'?order_no='.$order->po_number;
            $notification_data['to_user_id'] = $order->vendor_id;
            sendNotifications($notification_data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order Cancelled'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Order not Cancelled. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }

    private function reEvaluateRFQVendorsStatus($rfq_id){

        $latestIds = DB::table('rfq_vendor_quotations')
                    ->select(DB::raw('MAX(id) as id'))
                    ->where('rfq_id', $rfq_id)
                    ->where('status', 1)
                    ->groupBy('vendor_id')
                    ->pluck('id')->toArray();
        // 
        $rfq_vendors = DB::table('rfq_vendors')
                    ->select('vendor_user_id', 'product_id', 'vendor_status')
                    ->where('rfq_id', $rfq_id)
                    ->get()->toArray();
        // 

        $rfq = Rfq::where('rfq_id', $rfq_id)
                ->select('id', 'rfq_id', 'buyer_id', 'buyer_rfq_status', 'created_at', 'updated_at')
                ->with([
                    'rfqVendorQuotations' => function ($q) use($latestIds) {
                        $q->select('id', 'rfq_id', 'vendor_id', 'rfq_product_variant_id', 'price', 'buyer_price', 'created_at', 'updated_at')
                            ->whereIn('id', $latestIds);
                    },
                    'rfqProducts'=> function ($q) {
                        $q->select('id', 'rfq_id', 'product_id');
                    },
                    'rfqProducts.productVariants'=> function ($q) use($rfq_id) {
                        $q->where('rfq_id', $rfq_id);
                    },
                    'rfqOrders'=> function ($q) {
                        $q->select('id', 'rfq_id', 'vendor_id', 'po_number')->where('order_status', 1);
                    },
                    'rfqOrders.order_variants'=> function ($q) {
                        $q->select('id', 'po_number', 'rfq_product_variant_id', 'order_quantity');
                    }
                ])
                ->first();
        // 
        unset($latestIds);
        
        if (!$rfq) {
            return ['update_vendor_rfq_status_wise' => [], 'is_rfq_qty_left' => 'no'];
        }
        $rfq_data = $rfq->toArray();
        unset($rfq);
        
        $db_vendor_rfq_status = [];
        $rfq_product_vendor = [];
        foreach ($rfq_vendors as $key => $value) {
            $db_vendor_rfq_status[$value->vendor_user_id] = $value->vendor_status;
            $rfq_product_vendor[$value->product_id][] = $value->vendor_user_id;
        }
        
        $is_vendor_quote_the_price = [];
        foreach ($rfq_data['rfq_vendor_quotations'] as $key => $value) {
            if(!empty($value['price'])){
                $is_vendor_quote_the_price[$value['vendor_id']] = !empty($value['buyer_price']) ? 'counter-offer' : 'quote';
            }
        }
        
        foreach ($db_vendor_rfq_status as $vendor_id => $vendor_status) {
            if(!isset($is_vendor_quote_the_price[$vendor_id])){
                $is_vendor_quote_the_price[$vendor_id] = "no";
            }
        }

        $variant_qty = [];
        $vendor_wise_order_qty = [];
        $order_variant_qty = [];
        foreach ($rfq_data['rfq_products'] as $key => $value) {
            foreach ($value['product_variants'] as $k => $variant) {
                $variant_qty[$variant['id']] = $variant['quantity'];
                $order_variant_qty[$variant['id']] = 0;

                if (isset($rfq_product_vendor[$value['product_id']])) {
                    $vendors = $rfq_product_vendor[$value['product_id']];
                    foreach ($vendors as $vendorId) {
                        if (!isset($vendor_wise_order_qty[$vendorId])) {
                            $vendor_wise_order_qty[$vendorId] = [];
                        }
                        $vendor_wise_order_qty[$vendorId][$variant['id']] = 0;
                    }
                }
            }
        }

        foreach ($rfq_data['rfq_orders'] as $key => $order) {
            $vendor_id = $order['vendor_id'];
            foreach ($order['order_variants'] as $k => $variant) {
                $vid = $variant['rfq_product_variant_id'];
                $qty = $variant['order_quantity'];
                if (!isset($order_variant_qty[$vid])) {
                    $order_variant_qty[$vid] = 0;
                }
                $order_variant_qty[$vid] += $qty;

                if (!isset($vendor_wise_order_qty[$vendor_id][$vid])) {
                    $vendor_wise_order_qty[$vendor_id][$vid] = 0;
                }
                $vendor_wise_order_qty[$vendor_id][$vid] += $qty;
            }
        }

        $is_vendor_have_order = [];
        $is_vendor_order_qty_completed = [];
        $buyer_rfq_status = $rfq_data['buyer_rfq_status'];

        foreach ($vendor_wise_order_qty as $vendor_id => $variants_order_qty) {
            $is_vendor_have_order[$vendor_id] = array_sum(array_values($variants_order_qty)) > 0 ? "yes" : "no";            

            $is_qty_left = false;
            foreach ($variants_order_qty as $variant_grp_id => $order_qty) {
                if(isset($order_variant_qty[$variant_grp_id]) && ($variant_qty[$variant_grp_id] - $order_variant_qty[$variant_grp_id])>0){
                    $is_qty_left = true;
                    break;
                }
            }
            if($is_qty_left == true){//still vendor have some product for send counter offer 
                $is_vendor_order_qty_completed[$vendor_id] = "no";
            }else{
                $is_vendor_order_qty_completed[$vendor_id] = "yes";
            }
        }

        $is_rfq_qty_left = "no";
        foreach ($vendor_wise_order_qty as $vendor_id => $variants_order_qty) {
            foreach ($variants_order_qty as $variant_grp_id => $order_qty) {
                if(isset($order_variant_qty[$variant_grp_id]) && ($variant_qty[$variant_grp_id] - $order_variant_qty[$variant_grp_id])>0){
                    $is_rfq_qty_left = "yes";
                    break 2;
                }
            }
        }

        unset($rfq_data);
        unset($order_variant_qty);
        unset($variant_qty);
        unset($rfq_product_vendor);
        unset($rfq_vendors);

        $update_vendor_rfq_status = array();
        foreach ($vendor_wise_order_qty as $vendor_id => $value) {
            $vendor_rfq_status = 1;
            if($is_vendor_have_order[$vendor_id]=="yes"){
                if($is_vendor_order_qty_completed[$vendor_id] == "yes"){
                    $vendor_rfq_status = 5; // order confirm with full qty
                }else{
                    if($buyer_rfq_status==10){//due to rfq manually closed, vendor will also partially closed
                        $vendor_rfq_status = 10;
                    }else{
                        $vendor_rfq_status = 9;  //rfq not closed, partial order
                    }
                }
            }else{
                if($buyer_rfq_status==10){//due to rfq manually closed, vendor will also closed
                    $vendor_rfq_status = 8;
                }else{
                    if($is_vendor_order_qty_completed[$vendor_id] == "yes"){
                        $vendor_rfq_status = 8; // order confirm with full qty to another vendor
                    }else if($is_vendor_quote_the_price[$vendor_id] == "quote"){
                        $vendor_rfq_status = 7;
                    }else if($is_vendor_quote_the_price[$vendor_id] == "counter-offer"){
                        $vendor_rfq_status = 4;
                    }else{
                        $vendor_rfq_status = 1;
                    }
                    if(in_array($vendor_rfq_status, array(4, 7)) && $db_vendor_rfq_status[$vendor_id]==6){
                        $vendor_rfq_status = 6;
                    }
                }
            }
            if($db_vendor_rfq_status[$vendor_id]!=$vendor_rfq_status){
                $update_vendor_rfq_status[$vendor_id] = $vendor_rfq_status;
            }
        }

        unset($is_vendor_quote_the_price);
        unset($is_vendor_have_order);
        unset($vendor_wise_order_qty);
        unset($is_vendor_order_qty_completed);
        unset($db_vendor_rfq_status);
        unset($buyer_rfq_status);

        $update_vendor_rfq_status_wise = array();
        foreach ($update_vendor_rfq_status as $vendor_id => $vendor_rfq_status) {
            $update_vendor_rfq_status_wise[$vendor_rfq_status][] = $vendor_id;
        }
        unset($update_vendor_rfq_status);

        return array('update_vendor_rfq_status_wise'=>$update_vendor_rfq_status_wise, 'is_rfq_qty_left'=>$is_rfq_qty_left);
    }

    
    // private function reEvaluateRFQVendorsStatusRaw($rfq_id)
    // {
    //     $cis = DB::table('rfqs')
    //         ->select([
    //             'rfqs.id',
    //             'rfqs.rfq_id',
    //             'rfqs.buyer_id',
    //             'rfqs.buyer_rfq_status',
    //             'rfqs.created_at',
    //             'rfqs.updated_at',

    //             // rfqVendorQuotations
    //             // 'rfq_vendor_quotations.id as quotation_id',
    //             // 'rfq_vendor_quotations.rfq_id as quotation_rfq_id',
    //             'rfq_vendor_quotations.vendor_id as quotation_vendor_id',
    //             'rfq_vendor_quotations.rfq_product_variant_id as quotation_variant_id',
    //             'rfq_vendor_quotations.price',
    //             'rfq_vendor_quotations.buyer_price',
    //             'rfq_vendor_quotations.created_at as quotation_created_at',
    //             'rfq_vendor_quotations.updated_at as quotation_updated_at',

    //             // rfqVendors
    //             // 'rfq_vendors.id as rfq_vendor_id',
    //             // 'rfq_vendors.rfq_id as vendor_rfq_id',
    //             'rfq_vendors.vendor_user_id',
    //             'rfq_vendors.product_id as vendor_product_id',
    //             'rfq_vendors.vendor_status',

    //             // rfqProducts
    //             // 'rfq_products.id as product_id',
    //             // 'rfq_products.rfq_id as product_rfq_id',
    //             'rfq_products.product_id as product_product_id',

    //             // rfqProducts.productVariants
    //             'rfq_product_variants.id as variant_id',
    //             'rfq_product_variants.product_id as variant_product_id',
    //             'rfq_product_variants.quantity as variant_quantity',

    //             // rfqOrders
    //             // 'orders.id as order_id',
    //             // 'orders.rfq_id as order_rfq_id',
    //             'orders.vendor_id as order_vendor_id',
    //             'orders.po_number',

    //             // rfqOrders.order_variants
    //             // 'order_variants.id as order_variant_id',
    //             'order_variants.po_number as order_variant_po_number',
    //             'order_variants.rfq_product_variant_id',
    //             'order_variants.order_quantity',
    //         ])
    //         ->leftJoin('rfq_vendor_quotations', function($join) {
    //             $join->on('rfqs.rfq_id', '=', 'rfq_vendor_quotations.rfq_id')
    //                 ->where('rfq_vendor_quotations.status', 1);
    //         })
    //         ->leftJoin('rfq_vendors', 'rfqs.rfq_id', '=', 'rfq_vendors.rfq_id')
    //         ->leftJoin('rfq_products', 'rfqs.rfq_id', '=', 'rfq_products.rfq_id')
    //         ->leftJoin('rfq_product_variants', function($join) use ($rfq_id) {
    //             $join->on('rfq_products.id', '=', 'rfq_product_variants.product_id')
    //                 ->where('rfq_product_variants.rfq_id', $rfq_id);
    //         })
    //         ->leftJoin('orders', function($join) {
    //             $join->on('rfqs.rfq_id', '=', 'orders.rfq_id')
    //                 ->where('orders.order_status', 1);
    //         })
    //         ->leftJoin('order_variants', 'orders.po_number', '=', 'order_variants.po_number')
    //         ->where('rfqs.rfq_id', $rfq_id)
    //         ->get();

    //     return $cis;
    // }
}
