<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\UnapprovedPOExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Division;
use App\Models\Order;
use App\Models\OrderVariant;
use Illuminate\Http\Request;
use App\Models\Rfq;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Traits\HasModulePermission;


class RFQUnapprovedOrderController extends Controller
{
    use HasModulePermission;

    public function poQuery($request, $company_id)
    {
        $query = Order::with([
            'order_variants.product',
            'rfq.buyerBranch',
            'buyer',
            'vendor'
        ])
            ->where('buyer_id', $company_id)
            ->where('order_status', 3)
            ->orderBy('created_at', 'desc');


        /***:-  filters  -:***/
        if ($request->po_number) {
            $query->where('po_number', 'like', "%{$request->po_number}%");
        }
        if ($request->rfq_no) {
            $query->where('rfq_id', 'like', "%{$request->rfq_no}%");
        }

        if ($request->category && $request->category != 0) {
            $ids = explode(',', $request->category);
            $query->whereHas('order_variants.product.category', fn($q) => $q->whereIn('id', $ids));
        }
        if ($request->division && $request->division != 0) {
            $ids = explode(',', $request->division);
            $query->whereHas('order_variants.product.division', fn($q) => $q->whereIn('id', $ids));
        }
        if ($request->branch) {
            $query->whereHas('rfq.buyerBranch', fn($q) => $q->where('branch_id', $request->branch));
        }
        if ($request->from_date && $request->to_date) {
            $from = \Carbon\Carbon::createFromFormat('d/m/Y', $request->from_date)->startOfDay();
            $to   = \Carbon\Carbon::createFromFormat('d/m/Y', $request->to_date)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }
        if ($request->product_name) {
            $query->whereHas('order_variants.product', fn($q) => $q->where('product_name', 'like', "%{$request->product_name}%"));
        }
        if ($request->vendor_name) {
            $query->whereHas('vendor', fn($q) => $q->where('legal_name', 'like', "%{$request->vendor_name}%"));
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->ensurePermission('UNAPPROVE_PO_LISTING', 'view', '1');
        $company_id = getParentUserId();
        if ($request->ajax()) {

            $query = clone $this->poQuery($request, $company_id);

            /***:- Pagination  -:***/
            $perPage = $request->length ?? 10;
            $page = ($request->start / $perPage) + 1;
            $orders = $query->paginate($perPage, ['*'], 'page', $page);


            /***:- Format for DataTables  -:***/
            $data = [];
            foreach ($orders as $item) {
                $data[] = [
                    'unapproved_order_no' => '<a href="' . route('buyer.unapproved-orders.approvePO', ['rfq_id' => $item->rfq_id]) . '?p=' . base64_encode($item->po_number) . '">' . $item->po_number . '</a>',
                    'buyer_order_number' => $item->buyer_order_number,
                    'rfq_no' => $item->rfq_id,
                    'uo_date' => $item->created_at->format('d/m/Y'),
                    'rfq_date' => $item->rfq?->created_at?->format('d/m/Y'),
                    'branch' => $item->rfq?->buyerBranch?->name,
                    'product' => $item->order_variants->pluck('product.product_name')->unique()->join(', '),
                    'buyer' => $item->buyer?->legal_name,
                    'vendor' => $item->vendor?->legal_name,
                    'order_value' => $item->vendor_currency . $item->order_total_amount,
                    'status' => '<span class="rfq-status rfq-generate">Order to approve</span>',
                ];
            }

            return response()->json([
                "draw" => intval($request->draw),
                "recordsTotal" => $orders->total(),
                "recordsFiltered" => $orders->total(),
                "data" => $data
            ]);
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


        $buyer_branch = DB::table('branch_details')
            ->select('id', 'branch_id', 'name')
            ->where("user_id", $company_id)
            ->where('user_type', 1)
            ->where('record_type', 1)
            ->where('status', 1)
            ->get();

        return view('buyer.unapproved-orders.index', compact('divisions', 'unique_category', 'buyer_branch'));
    }

    public function exportPOData(Request $request)
    {
        $this->ensurePermission('TO_GENERATE_UNAPPROVE_PO', 'view', '1');
        $company_id = getParentUserId();
        /***:- clone the query  -:***/
        $query = clone $this->poQuery($request, $company_id);
        $indentData = $query->orderBy('id', 'desc')->get();

        return Excel::download(new UnapprovedPOExport($indentData), 'Unapproved Orders' . date('d-m-Y') . '.xlsx');
    }

    public function create($rfq_id)
    {
        $this->ensurePermission('TO_GENERATE_UNAPPROVE_PO', 'view', '1');
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }

        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();
        if (!empty($auction)) {
            $auction_status = getAuctionStatus($auction->auction_date, $auction->auction_start_time, $auction->auction_end_time);
            if ($auction_status == 1) {
                return back()->with('error', 'The auction for RFQ ' . $rfq_id . ' has been created and is still in scheduled/progress.');
            }
        }
        unset($auction);
        if (in_array($rfq_data->buyer_rfq_status, [1, 5, 8, 10])) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' Unapproved Order is unable to open.');
        }

        $encoded = request('q');
        if (!$encoded) {
            session()->flash('error', "Missing encoded data");
            return redirect()->back();
        }
        // Decode Base64 string
        $decoded = base64_decode($encoded);
        if (!preg_match('/^(\d+-\d+,)*(\d+-\d+)$/', $decoded)) {
            session()->flash('error', "Decoded data format invalid");
            return redirect()->back();
        }

        $vendors = explode(',', $decoded);
        $vendor_variants = [];
        $vendor_ids = [];
        $variants = [];

        foreach ($vendors as $vendor) {
            list($vendorId, $variantId) = explode('-', $vendor);

            if (!isset($vendor_variants[$vendorId])) {
                $vendor_variants[$vendorId] = [];
            }
            $vendor_variants[$vendorId][] = $variantId;
            $vendor_ids[] = $vendorId;
            $variants[] = $variantId;
        }

        $vendor_ids = array_unique($vendor_ids);
        $variants = array_unique($variants);

        $vendor_data = [
            'vendor_variants' => $vendor_variants,
            'vendors' => $vendor_ids,
            'variants' => $variants
        ];

        $unapprovedOrder = Rfq::unapprovedOrder($rfq_id, $vendor_data);

        if (isset($unapprovedOrder['all_qty_over']) && $unapprovedOrder['all_qty_over'] == true) {
            session()->flash('error', "Please check unapproved order as all balance qty of this RFQ has moved there.");
            return redirect()->to(route('buyer.dashboard'));
        }

        $uom = getUOMList();

        $taxes = DB::table("taxes")
            ->select("id", "tax")
            ->pluck("tax", "id")->toArray();

        return view('buyer.unapproved-orders.create', compact('uom', 'taxes', 'unapprovedOrder'));
    }



    public function sanitizeTheRequest($request)
    {
        $forms = $request->input('forms', []);

        if (empty($forms)) {
            return response()->json(['error' => 'No form data received'], 400);
        }

        /***:- Convert serialized arrays to key-value  -:***/
        $parsedForms = [];
        foreach ($forms as $form) {
            $parsedForms[] = collect($form)->pluck('value', 'name')->toArray();
        }

        $vendors = [];

        foreach ($parsedForms as $vendor) {
            $normalized = $vendor;
            $variants = [];

            foreach ($vendor as $key => $value) {
                if (preg_match('/^variants\[(\d+)\]\[(.+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $variants[$index][$field] = $value;
                    unset($normalized[$key]);
                }
            }

            $normalized['variants'] = array_values($variants); // reset indexes
            $vendors[] = $normalized;
        }

        return $vendors;
    }

    public function downloadPOPdf(Request $request, $rfq_id)
    {
        $this->ensurePermission('TO_GENERATE_UNAPPROVE_PO', 'view', '1');
        $vendors = $this->sanitizeTheRequest($request);
        $pdf = Pdf::loadView('buyer.unapproved-orders.download-po-pdf', [
            'vendors' => $vendors,
            'preparedBy' => auth()->user()->name,
            'approvedBy' => auth()->user()->name,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("Unapproved_PO_{$rfq_id}.pdf");
    }

    public function downloadPOPdf_working_with_database($rfq_id)
    {
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $parent_user_id)
            ->first();

        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }

        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();

        if (!empty($auction)) {
            $auction_status = getAuctionStatus($auction->auction_date, $auction->auction_start_time, $auction->auction_end_time);
            if ($auction_status == 1) {
                return back()->with('error', 'The auction for RFQ ' . $rfq_id . ' has been created and is still in scheduled/progress.');
            }
        }

        if (in_array($rfq_data->buyer_rfq_status, [1, 5, 8, 10])) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' Unapproved Order is unable to open.');
        }

        $encoded = request('q');
        if (!$encoded) {
            return back()->with('error', "Missing encoded data");
        }

        // Decode Base64 string
        $decoded = base64_decode($encoded);
        if (!preg_match('/^(\d+-\d+,)*(\d+-\d+)$/', $decoded)) {
            return back()->with('error', "Decoded data format invalid");
        }

        // Extract vendor/variant pairs
        $vendors = explode(',', $decoded);
        $vendor_variants = [];
        $vendor_ids = [];
        $variants = [];

        foreach ($vendors as $vendor) {
            [$vendorId, $variantId] = explode('-', $vendor);

            $vendor_variants[$vendorId][] = $variantId;
            $vendor_ids[] = $vendorId;
            $variants[] = $variantId;
        }

        $vendor_ids = array_unique($vendor_ids);
        $variants = array_unique($variants);

        $vendor_data = [
            'vendor_variants' => $vendor_variants,
            'vendors' => $vendor_ids,
            'variants' => $variants
        ];

        $unapprovedOrder = Rfq::unapprovedOrder($rfq_id, $vendor_data);

        if (!empty($unapprovedOrder['all_qty_over'])) {
            return redirect()->route('buyer.dashboard')
                ->with('error', "Please check unapproved order as all balance qty of this RFQ has moved there.");
        }

        $uom = getUOMList();
        $taxes = DB::table("taxes")
            ->select("id", "tax")
            ->pluck("tax", "id")->toArray();

        // Generate PDF
        $pdf = Pdf::loadView('buyer.unapproved-orders.download-po-pdf', [
            'uom' => $uom,
            'taxes' => $taxes,
            'unapprovedOrder' => $unapprovedOrder,
            'preparedBy' => auth()->user()->name,
            'approvedBy' => auth()->user()->name,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("Unapproved_PO_{$rfq_id}.pdf");
    }

    /***:- generate unapproved purchase order number  -:***/
    public function generateUnapprovedPoId($rfqNumber)
    {
        $rfqLastUnappPo = DB::table('rfqs as a')
            ->join('orders as o', 'a.rfq_id', '=', 'o.rfq_id')
            ->where('a.rfq_id', $rfqNumber)
            ->whereNotNull('o.po_number')
            ->where('a.record_type', 2)
            ->where('o.order_status', 3)
            ->orderByDesc('o.id')
            ->select('o.po_number')
            ->first();

        if (empty($rfqLastUnappPo)) {
            $lastNum = 1;
        } else {
            $rfqLastUnappPoNo = $rfqLastUnappPo->po_number;
            $parts = explode("/", $rfqLastUnappPoNo);
            $lastNum = isset($parts[1]) ? ((int)$parts[1] + 1) : 1;
        }

        // Format: UO-RFQ0000175/01
        return "UO-" . $rfqNumber . "/" . str_pad($lastNum, 2, "0", STR_PAD_LEFT);
    }


    public function generatePO(Request $request)
    {
        $this->ensurePermission('TO_GENERATE_UNAPPROVE_PO', 'add', '1');

        /***:- validate the request  -:***/
        $request->validate([
            'rfq_id' => 'required|exists:rfqs,rfq_id',
            'vendor_id' => 'required|integer|exists:users,id',
            'order_price_basis' => 'required',
            'order_payment_term' => 'required',
            'order_delivery_period' => 'required',

            'order_total_amount' => 'required|numeric|min:0',

            'variants' => 'required|array',
            'variants.*.rfq_id' => 'required',
            'variants.*.product_id' => 'required',
            'variants.*.rfq_product_variant_id' => 'required',
            'variants.*.rfq_quotation_variant_id' => 'required',

            'variants.*.order_quantity' => 'required|integer|min:1',
            'variants.*.order_mrp' => 'required|numeric|min:0|max:99999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'variants.*.order_discount' => 'required|numeric|min:0|max:100|regex:/^\d+(\.\d{1,2})?$/',
            'variants.*.order_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',

            'variants.*.product_gst' => 'required|numeric|min:0',
            'variants.*.rfq_id' => 'required|exists:rfqs,rfq_id',
        ]);



        DB::beginTransaction();
        try {
            $grandTotal = 0;
            $po_number = $this->generateUnapprovedPoId($request->rfq_id);
            $company_id = getParentUserId();

            /***:- Create the order  -:***/
            $order = Order::create([
                'vendor_id' => $request->vendor_id,
                'rfq_id' => $request->rfq_id,
                'buyer_id' => $company_id,
                'buyer_user_id' => Auth::user()->id,
                'order_total_amount' => 0,
                'order_status' => 3,
                "order_price_basis" => $request->order_price_basis,
                "order_payment_term" => $request->order_payment_term,
                "order_delivery_period" => $request->order_delivery_period,
                // "order_guarantee_warranty" => $request->order_guarantee_warranty,
                "order_remarks" => $request->order_remarks,
                "order_add_remarks" => $request->order_add_remarks,
                "buyer_order_number" => $request->buyer_order_number,
                "vendor_currency" => $request->vendor_currency,
                "po_number" => $po_number
            ]);

            /***:- Insert variants  -:***/
            $orderVariant = [];
            foreach ($request->variants as $variantId => $variant) {
                $qty = max(0, (float) $variant['order_quantity']);
                $mrp = max(0, (float) $variant['order_mrp']);
                $discount = min(max(0, (float) $variant['order_discount']), 100);
                $price = isset($variant['order_price']) ? max(0, (float) $variant['order_price']) : $mrp;


                /***:- calculate the total amount  -:***/
                $discountedPrice = $price - ($price * $discount / 100);
                $amount = $qty * $discountedPrice;

                $gst = isset($variant['product_gst']) ? (float) $variant['product_gst'] : 0;
                if ($gst > 0) {
                    $amount += ($amount * $gst / 100);
                }

                $grandTotal += $amount;
                $orderVariant[] = [
                    "rfq_product_variant_id" => $variant['rfq_product_variant_id'],
                    "rfq_quotation_variant_id" => $variant['rfq_quotation_variant_id'],
                    "rfq_id" => $variant['rfq_id'],
                    "product_id" => $variant['product_id'],
                    "order_quantity" => $variant['order_quantity'],
                    "order_mrp" => $variant['order_mrp'],
                    "order_discount" => $variant['order_discount'],
                    "order_price" => $variant['order_price'],
                    "product_gst" => $variant['product_gst'],
                    "po_number" => $po_number
                ];
            }

            /***:- bulk insert  -:***/
            OrderVariant::insert($orderVariant);

            /***:- Update order total  -:***/
            $order->update(['order_total_amount' => $grandTotal]);
            DB::commit();

            return response()->json(['status' => true, 'message' => 'Order generated successfully!', 'po_number' => $po_number, 'url' => route('buyer.unapproved-orders.approvePO', ['rfq_id' => $request->rfq_id])]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }


    public function deletePO(Request $request)
    {
        $this->ensurePermission('CANCEL_ORDER', 'delete', '1');

        DB::beginTransaction();
        try {
            Order::where('po_number', $request->po_number)->delete();
            OrderVariant::where('po_number', $request->po_number)->delete();
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Order delete successfully!', 'url' => route('buyer.unapproved-orders.list')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function approvePO(Request $request, $rfq_id)
    {
        $this->ensurePermission('TO_CONFIRM_ORDER', 'edit', '1');
        $po = $request->p;
        $parent_user_id = getParentUserId();

        /***:- get rfq details  -:***/
        $rfq_data = Rfq::where('record_type', 2)
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $parent_user_id)
            ->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }

        if (!$po) {
            session()->flash('error', "Missing encoded data");
            return redirect()->back();
        }

        /***:- Decode Base64 string  -:***/
        $decodedPO = base64_decode($po);

        $orders = Order::with(['order_variants', 'rfq', 'buyer', 'vendor', 'rfq.buyerBranch'])
            ->where('rfq_id', $rfq_id)
            ->where('po_number', $decodedPO)
            ->where('order_status', 3)
            ->first();

        if (!$orders) {
            return back()->with('error', 'No unapproved orders found for this RFQ.');
        }

        return view('buyer.unapproved-orders.approve', compact('orders', 'rfq_data'));
    }
}
