<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ManualOrder;
use App\Models\Vendor;
use App\Models\OrdersPi;
use App\Models\Rfq;
use App\Models\ManualOrderProduct;
use App\Models\Inventories;
class OrderController extends Controller
{
    public function rfqOrder(Request $request) {
       $query = Order::with('order_variants','buyer')->where('vendor_id', getParentUserId());

        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('order_no')){
            $query->where('po_number', 'like', "%$request->order_no%");
        }
        if ($request->filled('rfq_no')){
            $query->where('rfq_id', 'like', "%$request->rfq_no%");
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

        $query->where('order_status', '!=', 3);

        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('vendor.order.partials.rfq-table', compact('results'))->render();
        }
        return view('vendor.order.rfq-index',compact('results'));
    }

    public function rfqOrderView(Request $request,$id) {

        $order = Order::with(
            [
                'order_variants.frq_variant',
                'order_variants.frq_quotation_variant'=>function($q){
                    //$q->where('vendor_id', getParentUserId());
                },
                'buyer'
            ])->where('id', $id)->first();
        return view('vendor.order.rfq-view',compact('order'));
    }

    public function rfqOrderPrint(Request $request,$id) {
       // echo '<pre>';
        $order = Order::with(['vendor','vendor.user','buyer','buyer.users','rfq','rfq.buyer_branchs','order_variants.frq_variant','order_variants.frq_quotation_variant'])->where('id', $id)->first();
       //print_r($order);die;
        return view('vendor.order.rfq-pdf',compact('order'));
    }

    public function directOrder(Request $request) {

        $query = ManualOrder::with('order_products','buyer')->where('vendor_id', getParentUserId());

        if ($request->filled('buyer_name'))
        {
            $legal_name=$request->buyer_name;
            $query->whereHas('buyer', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('order_no')){
            $query->where('manual_po_number', 'like', "%$request->order_no%");
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
            return view('vendor.order.partials.direct-table', compact('results'))->render();
        }
        return view('vendor.order.direct-index', compact('results'));
    }

    public function directOrderView(Request $request,$id) {

        $order = ManualOrder::with('order_products','order_products.inventory','buyer')->where('id', $id)->first();
        //print_r($order);die;
        return view('vendor.order.direct-view',compact('order'));
    }

    public function directOrderPrint(Request $request, $id) {
        // echo '<pre>';
        $order = ManualOrder::with([
                            'order_products',
                            'order_products.vendorProducts' => function($q) {
                                $q->select('product_id', 'vendor_id', 'hsn_code');
                                $q->where('vendor_id', getParentUserId());
                            },
                            'order_products.inventory',
                            'buyer'
                            ])
                            ->where('id', $id)
                            ->first();
        // echo "<pre>";
        // print_r($order);die;
        $vendor=Vendor::with(['vendor_country','vendor_state','vendor_city'])->where('user_id',getParentUserId())->first();
        // print_r($vendor);die;
        return view('vendor.order.direct-pdf',compact('order','vendor'));
    }

    // public function uploadPiAttachment1(Request $request) {
    //     $order_number = $request->order_number;
    //     $order_type = $request->order_type;
    //     if ($request->hasFile('pi_attachment')) {
    //         $file = $request->file('pi_attachment');
    //         $name = time() . '.' . $file->getClientOriginalExtension();
    //         $file->move(public_path('uploads/pi-order/'), $name);
    //         $result = array('status' => true, 'file_name' => $name,'file_url'=>url('uploads/pi-order/'.$name));
    //         $buyer_branch='';
    //         if($order_type=='direct_order'){
    //             $order_data=ManualOrder::where('manual_po_number', $order_number)->where('vendor_id', getParentUserId())->first();
    //             $orderProduct=ManualOrderProduct::select('inventory_id')->where('manual_order_id', $order_data->id)->first();
    //             $inventory_id=$orderProduct->inventory_id;
    //             $inventory=Inventories::find($inventory_id);
    //             $buyer_branch=$inventory->buyer_branch_id;
    //         }
    //         if($order_type=='rfq_order'){
    //             $order_data=Order::where('po_number', $order_number)->where('vendor_id', getParentUserId())->first();
    //             $rfq_id=$order_data->rfq_id;
    //             $rfq=Rfq::select('buyer_branch')->where('rfq_id',$rfq_id)->first();
    //             $buyer_branch=$rfq->buyer_branch;
    //             if(empty($order_data) || $order_data->order_status!=1){
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'This order is cancelled or not found, Please refresh the page.',
    //                 ]);
    //             }
    //         }
    //         $order=new OrdersPi;
    //         $order->order_number=$order_number;
    //         $order->buyer_id=$order_data->buyer_id;
    //         $order->vendor_id=getParentUserId();
    //         $order->pi_attachment=$name;
    //         $order->order_date=$order_data->created_at;
    //         $order->pi_date=date('Y-m-d h:i:s');
    //         $order->vendor_user_id=auth()->user()->id;
    //         $order->buyer_branch_id=$buyer_branch;
    //         $order->save();
    //         $result = array(
    //             'status' => true, 
    //             'message' => "PI Attachment uploaded successfully and sent to the buyer", 
    //             'file_name' => $name,
    //             'file_url' => url('uploads/pi-order/'.$name)
    //         );
    //     } else {
    //         $result = array(
    //             'status' => false, 
    //             'message'=>'Please select PI Attachment', 
    //             'file_name' => "File upload failed.",
    //             'file_url'=>null
    //         );
    //     }
    //     return response()->json($result);
    // }

    public function uploadPiAttachment(Request $request) {
        $order_number = $request->order_number;
        $order_type = $request->order_type;
        $vendor_id = getParentUserId();

        if (!$request->hasFile('pi_attachment')) {
            return response()->json([
                'status' => false,
                'message' => 'Please select PI Attachment'
            ]);
        }
        if (!$order_number || !in_array($order_type, ['direct_order', 'rfq_order'])) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong...., Please refresh the page.'
            ]);
        }

        if ($order_type == 'direct_order') {
            $order_data = ManualOrder::where('manual_po_number', $order_number)->where('vendor_id', $vendor_id)->first();
            if(empty($order_data) || $order_data->order_status!=1){
                return response()->json([
                    'status' => false,
                    'message' => 'This order is cancelled or not found, Please refresh the page.',
                ]);
            }
            $orderProduct = ManualOrderProduct::select('inventory_id')->where('manual_order_id', $order_data->id)->first();
            $inventory_id = $orderProduct->inventory_id;
            $inventory = Inventories::find($inventory_id);
            $buyer_branch = $inventory->buyer_branch_id;

            $orderPi = OrdersPi::where('order_number', $order_number)
                                ->where('vendor_id', $vendor_id)
                                ->first();

            if (!$orderPi) {
                // Create if not exists
                $orderPi = new OrdersPi();
                $orderPi->order_number = $order_number;
                $orderPi->vendor_id = $vendor_id;
                $orderPi->buyer_id = $order_data->buyer_id;
                $orderPi->order_date = $order_data->created_at ?? now();
            }
        } else if ($order_type == 'rfq_order') {
            $order_data = Order::where('po_number', $order_number)->where('vendor_id', $vendor_id)->first();
            if(empty($order_data) || $order_data->order_status!=1){
                return response()->json([
                    'status' => false,
                    'message' => 'This order is cancelled or not found, Please refresh the page.',
                ]);
            }
            $rfq_id = $order_data->rfq_id;
            $rfq = Rfq::select('buyer_branch')->where('rfq_id', $rfq_id)->first();
            $buyer_branch = $rfq->buyer_branch;

            // RFQ order check, update
            $orderPi = OrdersPi::where('order_number', $order_number)
                                ->where('vendor_id', $vendor_id)
                                ->first();

            if (!$orderPi) {
                // Create if not exists
                $orderPi = new OrdersPi();
                $orderPi->order_number = $order_number;
                $orderPi->vendor_id = $vendor_id;
                $orderPi->buyer_id = $order_data->buyer_id;
                $orderPi->order_date = $order_data->created_at ?? now();
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid order type.'
            ]);
        }

        // Upload new file
        $file = $request->file('pi_attachment');
        $newName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/pi-order/'), $newName);

        // Remove previous file if exists
        if (!empty($orderPi->pi_attachment)) {
            $oldPath = public_path('uploads/pi-order/' . $orderPi->pi_attachment);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $orderPi->updated_by = auth()->user()->id;
        }

        // Update or create record
        $orderPi->pi_attachment = $newName;
        $orderPi->buyer_branch_id=$buyer_branch;
        $orderPi->pi_date = now();
        $orderPi->vendor_user_id = auth()->user()->id;
        $orderPi->save();

        return response()->json([
            'status' => true,
            'message' => 'PI Attachment updated successfully.',
            'file_name' => $newName,
            'file_url' => url('uploads/pi-order/' . $newName)
        ]);
    }


    public function deletePiAttachment(Request $request)
    {
        $order_type = $request->order_type;
        $order_number = $request->order_number;

        // Validate input
        if (empty($order_number) || !in_array($order_type, ['manual_order', 'rfq_order'])) {
            return response()->json([
                'status' => false,
                'message' => "Something went wrong...., Please refresh the page"
            ]);
        }

        $vendor_id = getParentUserId();
        $orderPi = OrdersPi::where('vendor_id', $vendor_id)
                        ->where('order_number', $order_number)
                        ->first();

        if (!$orderPi) {
            return response()->json([
                'status' => false,
                'message' => "PI Attachment not found, please try again later."
            ]);
        }

        // echo "<pre>";
        // print_r($orderPi);die;

        // Remove the file if exists
        if (!empty($orderPi->pi_attachment)) {
            $file_path = public_path('uploads/pi-order/' . $orderPi->pi_attachment);
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }

        // Delete the DB record
        $deleted = $orderPi->delete();

        if ($deleted) {
            return response()->json([
                'status' => true,
                'message' => "PI Attachment deleted successfully"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Failed to delete PI Attachment, Please try again later."
            ]);
        }
    }

}
