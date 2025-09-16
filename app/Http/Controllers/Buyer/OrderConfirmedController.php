<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderVariant;
use App\Models\Division;
use App\Models\Category;
use App\Models\Rfq;
use App\Models\Vendor;
use DB;
use App\Traits\HasModulePermission;
use Illuminate\Support\Facades\Validator;
use App\Helpers\EmailHelper;
class OrderConfirmedController extends Controller
{
    use HasModulePermission;

    protected function baseQuery(Request $request)
    {
        $query = Order::with('order_variants.product', 'vendor', 'rfq', 'order_confirmed_by')
            ->where('buyer_id', getParentUserId())
            ->where('order_status', '!=', '3');

        if ($request->filled('order_no')) {
            $query->where('po_number', 'like', '%' . $request->order_no . '%');
        }
        if ($request->filled('rfq_no')) {
            $query->where('rfq_id', 'like', '%' . $request->rfq_no . '%');
        }
        if ($request->filled('division')) {
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('division_id', $request->division);
                });
            });
        }
        if ($request->filled('category')) {
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            });
        }
        if ($request->filled('branch')) {
            $query->whereHas('rfq', function ($q) use ($request) {
                $q->where('buyer_branch', $request->branch);
            });
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }
        if ($request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if (!$request->filled('from_date') && $request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        if ($request->filled('product_name')) {
            $query->whereHas('order_variants', function ($q) use ($request) {
                $q->whereHas('product', function ($q) use ($request) {
                    $q->where('product_name', 'like', "%$request->product_name%");
                });
            });
        }
        if ($request->filled('vendor_name')) {
            $legal_name = $request->vendor_name;
            $query->whereHas('vendor', function ($q) use ($legal_name) {
                $q->where('legal_name', 'like', "%$legal_name%");
            });
        }
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->ensurePermission('ORDERS_CONFIRMED_LISTING', 'view', '1');

        $query = $this->baseQuery($request);

        $order = $request->order;
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
        $branchs = getBuyerBranchs();

        return view('buyer.rfq.order_confirmed.index', compact('results', 'divisions', 'unique_category', 'branchs'));
    }

    public function exportTotal(Request $request)
    {
        $total = $this->baseQuery($request)->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request)
    {
        $limit = intval($request->input('limit'));
        $lastId = $request->input('last_id');

        $query = $this->baseQuery($request)->orderBy('id');
        if ($lastId) {
            $query->where('id', '>', $lastId);
        }

        $data_list = $query->take($limit)->get();

        $result = [];
        foreach ($data_list as $row) {
            $result[] = [
                $row->po_number ?? '-',
                $row->buyer_order_number,
                $row->rfq_id ?? '-',
                date('d/m/Y', strtotime($row->created_at)),
                $row->rfq ? date('d/m/Y', strtotime($row->rfq->created_at)) : '-',
                !empty($row->rfq->buyer_branch) ? getbuyerBranchById($row->rfq->buyer_branch)->name : '-',
                $row->order_variants->pluck('product.product_name')->filter()->unique()->join(', ') ?? '-',
                $row->order_confirmed_by->name ?? '-',
                $row->vendor->legal_name ?? '-',
                ($row->vendor_currency ?? '') . ($row->order_total_amount ? IND_money_format($row->order_total_amount) : '0'),
                $row->order_status == 2 ? 'Cancelled' : '',
            ];
        }

        $lastRow = $data_list->last();
        return response()->json([
            'data' => $result,
            'last_id' => $lastRow->id ?? null,
        ]);
    }

    public function view(Request $request,$id) {
        $this->ensurePermission('ORDERS_CONFIRMED_LISTING', 'view', '1');
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
        $this->ensurePermission('ORDERS_CONFIRMED_LISTING', 'view', '1');
        $order = Order::with(['vendor','vendor.user','buyer','buyer.users','rfq','rfq.buyer_branchs','order_variants.frq_variant','order_variants.frq_quotation_variant'])->where('id', $id)->first();
        // echo '<pre>';
        // print_r($order);die;
        return view('buyer.rfq.order_confirmed.pdf',compact('order'));
    }
    public function cancel(Request $request, $id) {
        $this->ensurePermission('CANCEL_ORDER', 'edit', '1');
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

    public function approve(Request $request)
    {   
        $this->ensurePermission('TO_CONFIRM_ORDER', 'add', '1');
        // $rules = [
        //     'po_number'            => 'required|string',
        //     'order_quantity'       => 'required|array',
        //     'order_quantity.*'     => 'required|array',
        //     // 'order_quantity.*.*'   => 'required|numeric|min:0.1',

        //     'order_rate'           => 'required|array',
        //     'order_rate.*'         => 'required|array',
        //     // 'order_rate.*.*'       => 'required|numeric|min:1',

        //     'order_price_basis'    => 'required|string|max:200',
        //     'order_payment_term'   => 'required|string|max:200',
        //     'order_delivery_period'=> 'required|integer|min:1|max:999',
        // ];
        $rules = [
            'po_number'           => 'required|string',
            'order_quantity'      => 'required|array',
            'order_quantity.*'    => 'required|numeric|min:0.1',
            'order_rate'          => 'required|array',
            'order_rate.*'        => 'required|numeric|min:1',
            'order_price_basis'   => 'required|string|max:200',
            'order_payment_term'  => 'required|string|max:200',
            'order_delivery_period'=> 'required|integer|min:1|max:999',
        ];
        $messages = [
            'order_quantity.*.required' => 'Quantity for each variant is required.',
            'order_quantity.*.numeric'  => 'Quantity must be a numeric value.',
            'order_rate.*.*.min'          => 'Rate must be at least 1.',
            'po_number.required'          => 'Purchase Order number is mandatory.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            // Get all error messages as array
            $errors = $validator->errors()->all();

            // Concatenate or pick the first error message
            $message = implode(' ', $errors);

            return response()->json([
                'status' => false,
                'message' => $message,
            ]);
        }

        $parent_user_id = getParentUserId();
        $po_number = $request->po_number;

        $order_quantity = $request->order_quantity;
        $order_rate = $request->order_rate;
        $order_price_basis = $request->order_price_basis;
        $order_payment_term = $request->order_payment_term;
        $order_delivery_period = $request->order_delivery_period;

        $is_order_exists = DB::table('orders')->where('po_number', $po_number)->where('buyer_id', $parent_user_id)->where('order_status', 3)->first();
        $order_variants = DB::table('order_variants')->where('po_number', $po_number)->get()->keyBy('rfq_product_variant_id')->toArray();
        if (empty($is_order_exists) || empty($order_variants)) {
            return response()->json([
                'status' => false,
                'message' => 'Unapproved Order not found',
            ]);
        }

        $errors = $this->validateOrderQty($order_variants, $order_quantity);
        $requested_order = $this->requestedOrder($request);
        
        // echo "<pre>";
        // print_r($_POST);
        // die;

        if (!empty($errors)) {
            // return response()->json(['status' => false, 'message' => implode(' ', $errors)], 422);
            return response()->json(['status' => false, 'message' => "Some of the product quantity is exceeded, Please refresh the page and try again"], 422);
        }

        DB::beginTransaction();

        try {
            $new_po_number = $this->generatePONumber($is_order_exists->rfq_id, $parent_user_id);
            $po_insert_id = $this->updateToPO($request, $is_order_exists, $order_variants, $requested_order, $new_po_number);
            $evaluated_vendors_status = $this->reEvaluateRFQVendorsStatus($is_order_exists->rfq_id);
            
            $is_rfq_qty_left = $evaluated_vendors_status['is_rfq_qty_left'];
            $buyer_rfq_status = 5;
            if ($is_rfq_qty_left == "yes") {
                $buyer_rfq_status = 9;
            }
            DB::table('rfqs')
                ->where('rfq_id', $is_order_exists->rfq_id)
                ->update([
                    'buyer_rfq_status' => $buyer_rfq_status
                ]);

            if(!empty($evaluated_vendors_status['update_vendor_rfq_status_wise'])){
                foreach ($evaluated_vendors_status['update_vendor_rfq_status_wise'] as $vend_rfq_status => $vendor_ids) {
                    DB::table("rfq_vendors")
                        ->where('rfq_id', $is_order_exists->rfq_id)
                        ->whereIn('vendor_user_id', array_values($vendor_ids))
                        ->update(['vendor_status' => $vend_rfq_status]);
                }
            }
            
            $notification_data = array();
            $notification_data['po_number'] = $new_po_number;
            $notification_data['message_type'] = 'Order Confirmed';
            $notification_data['notification_link'] = route('vendor.rfq_order.show', $po_insert_id);
            $notification_data['to_user_id'] = $is_order_exists->vendor_id;
            sendNotifications($notification_data);
                        
            DB::commit();

            $order = DB::table('orders')->where('po_number', $new_po_number)->where('buyer_id', $parent_user_id)->where('order_status', 1)->first();
            $this->sendPOEmail($order);

            return response()->json([
                'status' => true,
                'redirect_url' => route('buyer.rfq.order-confirmed.view', $po_insert_id),
                'message' => 'Purchase Order Generated Successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Generate Purchase Order, Please try again later. '.$e->getMessage(),
                'complete_message' => [
                    // 'exception' => get_class($e),
                    // 'message' => $e->getMessage(),
                    // 'code'    => $e->getCode(),
                    // 'file'    => $e->getFile(),
                    // 'line'    => $e->getLine(),
                    // 'trace'   => $e->getTraceAsString(),
                ],
            ]);
        }
    }
    private function validateOrderQty($order_variants, $order_quantity)
    {
        // Validation errors collector
        $errors = [];

        // Check if all DB variant IDs exist in post
        foreach ($order_variants as $variant_id => $variantData) {
            if (!isset($order_quantity[$variant_id])) {
                $errors[] = "Variant ID $variant_id is missing in the posted quantities.";
                continue;
            }
            // Extract posted quantity (assuming first index [0])
            $postedQuantity = floatval($order_quantity[$variant_id] ?? 0);
            $dbQuantity = floatval($variantData->order_quantity);
            
            if ($postedQuantity > $dbQuantity) {
                $errors[] = "Quantity for variant ID $variant_id exceeds allowed order quantity ($dbQuantity).";
            }
        }

        return $errors;
    }
    private function requestedOrder($request)
    {
        $order_quantity = $request->order_quantity;
        $order_mrp      = $request->order_mrp;
        $order_discount = $request->order_discount;
        $order_rate     = $request->order_rate;

        $requesting_order_id_wise_qty = [];

        foreach ($order_quantity as $variant_id => $qArr) {
            $quantity = isset($qArr) ? $qArr : 0;
            $mrp      = isset($order_mrp[$variant_id]) ? $order_mrp[$variant_id] : 0;
            $discount = isset($order_discount[$variant_id]) ? $order_discount[$variant_id] : 0;
            $price    = isset($order_rate[$variant_id]) ? $order_rate[$variant_id] : 0;

            $requesting_order_id_wise_qty[$variant_id] = [
                'quantity' => number_format((float)$quantity, 2, '.', ''),
                'mrp'      => number_format((float)$mrp, 2, '.', ''),
                'discount' => number_format((float)$discount, 2, '.', ''),
                'price'    => number_format((float)$price, 2, '.', ''),
            ];
        }
        return $requesting_order_id_wise_qty;
    }
    private function generatePONumber($rfq_id, $parent_user_id)
    {
        $orderCount = DB::table('orders')
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $parent_user_id)
            ->whereIn('order_status', [1, 2])
            ->count();
        
        return "O-" . $rfq_id . "/" . str_pad($orderCount + 1, 2, '0', STR_PAD_LEFT);
    }
    private function updateToPO($request, $is_order_exists, $order_variants, $requested_order, $new_po_number)
    {
        $parent_user_id = getParentUserId();
        $po_number = $request->po_number;

        $po_insert_id = DB::table('orders')->insertGetId([
            'rfq_id' => $is_order_exists->rfq_id,
            'vendor_id' => $is_order_exists->vendor_id,
            'po_number' => $new_po_number,
            'buyer_order_number' => $request->buyer_order_number,
            'order_total_amount' => 0,
            'order_status' => 1,
            'buyer_id' => $parent_user_id,
            'buyer_user_id' => auth()->user()->id,
            'unapprove_by_user_id' => $is_order_exists->buyer_user_id,
            'order_price_basis' => $request->order_price_basis,
            'order_payment_term' => $request->order_payment_term,
            'order_delivery_period' => $request->order_delivery_period,
            'order_remarks' => $request->order_remarks,
            'order_add_remarks' => $request->order_add_remarks,
            'guranty_warranty' => $request->order_gurantee_warranty,
            'vendor_currency' => $is_order_exists->vendor_currency,
            'int_buyer_vendor' => $is_order_exists->int_buyer_vendor
        ]);

        $po_variants = array();
        $total_amount = 0;
        $unapproved_po_total_amount = 0;
        $is_complete_order = true;
        foreach ($order_variants as $variant_id => $value) {
            $quantity = $requested_order[$variant_id]['quantity'];
            $mrp = $requested_order[$variant_id]['mrp'];
            $discount = $requested_order[$variant_id]['discount'];
            $price = $requested_order[$variant_id]['price'];
            
            $gst = $value->product_gst;
            $amount = $price * $quantity;
            $gst_amount = ($amount * $gst) / 100;
            $t_amount = $amount + $gst_amount;
            $total_amount += $t_amount;

            if($quantity < $value->order_quantity) {
                $is_complete_order = false;
                $left_qty = $value->order_quantity - $quantity;
                DB::table('order_variants')->where('po_number', $po_number)
                    ->where('id', $value->id)
                    ->update([
                        'order_quantity' => $left_qty,
                    ]);

                $po_variants[] = [
                    'po_number' => $new_po_number,
                    'rfq_id' => $value->rfq_id,
                    'product_id' => $value->product_id,
                    'rfq_product_variant_id' => $value->rfq_product_variant_id,
                    'rfq_quotation_variant_id' => $value->rfq_quotation_variant_id,
                    'order_quantity' => $quantity,
                    'order_mrp' => $mrp,
                    'order_discount' => $discount,
                    'order_price' => $price,
                    'product_hsn_code' => $value->product_hsn_code,
                    'product_gst' => $value->product_gst
                ];

                $u_amount = $value->order_price * $left_qty;
                $u_gst_amount = ($u_amount * $gst) / 100;
                $u_t_amount = $u_amount + $u_gst_amount;
                $unapproved_po_total_amount += $u_t_amount;
            }else{
                DB::table('order_variants')->where('po_number', $po_number)
                    ->where('id', $value->id)
                    ->update([
                        'po_number' => $new_po_number,
                        'order_mrp' => $mrp,
                        'order_discount' => $discount,
                        'order_price' => $price,
                        'created_at' => \Carbon\Carbon::now(),
                    ]);
            }
        }

        if(!empty($po_variants)){
            DB::table('order_variants')->insert($po_variants);
        }

        $total_amount = number_format((float)$total_amount, 2, '.', '');
        DB::table('orders')->where('po_number', $new_po_number)
            ->where('order_status', 1)
            ->update([
                'order_total_amount' => $total_amount,
            ]);

        if($is_complete_order==false) {
            $unapproved_po_total_amount = number_format((float)$unapproved_po_total_amount, 2, '.', '');
            DB::table('orders')->where('po_number', $po_number)
                ->where('order_status', 3)
                ->update([
                    'order_total_amount' => $unapproved_po_total_amount,
                ]);
        }else{
            DB::table('orders')->where('po_number', $po_number)
                ->where('order_status', 3)
                ->delete();
        }
        return $po_insert_id;
    }
    private function sendPOEmail($order)
    {
        $vendor_data = Vendor::with(
                'user:id,email'
            )
            ->select('id', 'user_id', 'legal_name')
            ->where('user_id', $order->vendor_id)
            ->first()->toArray();
        //
        
        $subject = "Order Confirmed (Order No. " . $order->po_number . " )";

        $mail_data = vendorEmailTemplet('order-confirmation-email');
        $admin_msg = $mail_data->mail_message;

        $product_data = $this->getPOVariantHTMLForMail($order->po_number, get_currency_str($order->vendor_currency));

        $admin_msg = str_replace('$rfq_date_formate', now()->format('d/m/Y'), $admin_msg);
        $admin_msg = str_replace('$rfq_number', $order->rfq_id, $admin_msg);
        $admin_msg = str_replace('$buyer_name', session('legal_name'), $admin_msg);
        $admin_msg = str_replace('$vendor_name', $vendor_data['legal_name'], $admin_msg);
        $admin_msg = str_replace('$product_details', $product_data, $admin_msg);
        $admin_msg = str_replace('$dispatch_address', '', $admin_msg);
        $admin_msg = str_replace('$delivery_address', '', $admin_msg);
        $admin_msg = str_replace('$order_id', $order->po_number, $admin_msg);
        $admin_msg = str_replace('$order_date', now()->format('d/m/Y'), $admin_msg);
        $admin_msg = str_replace('$website_url', route("login"), $admin_msg);

        EmailHelper::sendMail($vendor_data['user']['email'], $subject, $admin_msg);
    }

    private function getPOVariantHTMLForMail($po_number, $currency_symbol){

        $po_variants = OrderVariant::with([
                            'product:id,product_name', 
                            'frq_variant:id,rfq_id,product_id,uom',
                            'frq_variant.uoms:id,uom_name',
                        ])
                        ->select('id', 'po_number', 'rfq_id', 'product_id', 'rfq_product_variant_id', 'order_quantity', 'order_price', 'product_gst')
                        ->where('po_number', $po_number)
                        ->get()->toArray();
        // 
        $mail_html = '';
        $total_price = 0;
        if(!empty($po_variants)){
            foreach ($po_variants as $key => $value) {
                $sub_total_price = $value['order_price'] * $value['order_quantity'];
                /*if ($value['product_gst'] != '') {
                    $sub_total_price = $sub_total_price + ($sub_total_price * $value['product_gst'] / 100);
                }*/
                $total_price += $sub_total_price;
                $sub_total_price = number_format((float)$sub_total_price, 2, '.', '');

                $mail_html.= '<tr class="td_class">
                                <td class="td_class">
                                  ' . $value['product']['product_name'] . '
                                </td>
                                <td class="td_class" style="text-align: center;">
                                  ' . $value['order_quantity'] . '
                                </td>
                                <td class="td_class" style="text-align: center;">
                                  '. $value['frq_variant']['uoms']['uom_name'] .'
                                </td>
                                <td class="td_class" style="text-align: center;">
                                ' . $currency_symbol .' '. IND_money_format($sub_total_price) . '
                                </td>
                            </tr>';
            }
            $po_total_amout = number_format((float)$total_price, 2, '.', '');
            $mail_html.='<tr>
                            <td colspan="3" class="td_class">Total</td>
                            <td class="td_class" style="text-align: center;">
                            ' . $currency_symbol .' '. IND_money_format($po_total_amout) . '
                            </td>
                        </tr>';
        }
        return $mail_html;
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

    
}
