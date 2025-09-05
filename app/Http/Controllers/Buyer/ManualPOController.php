<?php

namespace App\Http\Controllers\Buyer;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Carbon\Carbon;

use App\Models\{Tax, User, Vendor, ManualOrder, Inventories,ManualOrderProduct,Grn};
use App\Helpers\{NumberFormatterHelper, TruncateWithTooltipHelper};
use App\Http\Controllers\Buyer\InventoryController;

use App\Exports\ManualPoReportExport;
use App\Mail\ManualOrderConfirmationMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ExportService;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use Illuminate\Support\Facades\DB;



class ManualPOController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    public static function userCurrency(): void
    {
        $userId=(Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $user = User::with('currencyDetails')->find($userId);
        if ($user && $user->currencyDetails) {
            session([
                'user_currency' => [
                    'id' => $user->currencyDetails->id,
                    'symbol' => $user->currencyDetails->currency_symbol,
                ]
            ]);
        }
    }
    public function fetchInventoryDetails(Request $request)
    {
        $this->userCurrency();
        $inventories = collect();
        Inventories::whereIn('id', $request->input('ids'))
            ->with(['product:id,product_name', 'uom:id,uom_name'])
            ->select('id', 'product_id', 'specification', 'size', 'uom_id')
            ->orderBy('product_name', 'asc')
            ->chunk(500, function ($chunk) use (&$inventories) {
                $inventories = $inventories->concat($chunk);
            });
        $taxes = Tax::where('status', '1')->get(['id', 'tax']);
        return $inventories->isEmpty()
            ? response()->json(['status' => 'error', 'message' => 'No inventories found'], 404)
            : response()->json(['status' => 'success', 'data' => ['inventories' => $inventories->values(), 'taxes' => $taxes]]);
    }
    public function searchVendorByVendorname(Request $request)
    {
        $query = $request->input('q');

        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $results = collect();
        User::where('name', 'like', "%{$query}%")
            ->where('is_profile_verified', 1)
            ->where('is_verified', 1)
            ->where('user_type', 2)
            ->where('status', 1)
            ->select('id', 'name')
            ->chunk(500, function ($users) use (&$results) {
                $results = $results->concat($users);
            });

        return response()->json($results->values());
    }

    public function getVendorDetailsByName(Request $request){
        $userId = $request->input('id'); // assuming `id` = user_id

        if (!$userId) {
            return response()->json(['message' => 'User ID is required.'], 400);
        }
        $vendor = Vendor::where('user_id', $userId)
        ->with(['user', 'vendor_country', 'vendor_state', 'vendor_city'])
        ->first();

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        return response()->json([
            'name' => $vendor->user->name ?? '',
            'email' => $vendor->user->email ?? '',
            'mobile' => $vendor->user->mobile ?? 'N/A',
            'address' => $vendor->registered_address ?? '',
            'pincode' => $vendor->pincode ?? '',
            'city' => $vendor->vendor_city->city_name ?? '',
            'state' => $vendor->vendor_state->name ?? '',
            'state_code' => $vendor->vendor_state->state_code ?? '',
            'country' => $vendor->vendor_country->name ?? '',
            'gstin' => $vendor->gstin ?? '',
            'country_code' => $vendor->country_code ?? '',
            'legal_name' => $vendor->legal_name ?? '',
        ]);

    }

    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $request->validate([
            'vendor_user_id'     => ['required', 'exists:users,id'],

            'inventory_id'       => ['required', 'array'],
            'inventory_id.*'     => ['required', 'exists:inventories,id'],

            'qty'                => ['required', 'array'],
            'qty.*'              => ['required', 'numeric', 'min:0.01', new NoSpecialCharacters(false)],

            'rate'               => ['required', 'array'],
            'rate.*'             => ['required', 'numeric', 'min:0.01', new NoSpecialCharacters(false)],

            'gst'                => ['required', 'array'],
            'gst.*'              => ['required', 'exists:taxes,id'],

            'paymentTerms'       => ['required', 'string', 'max:2000', new NoSpecialCharacters(false)],
            'priceBasis'         => ['required', 'string', 'max:2000', new NoSpecialCharacters(false)],
            'deliveryPeriod'     => ['required', 'numeric', 'min:1', 'max:999', new NoSpecialCharacters(false)],

            'remarks'            => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
            'additionalRemarks'  => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
        ]);
        try {
            DB::beginTransaction();
            $userId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
            $user = User::with('buyer')->find($userId);
            $orgShortCode = $user->buyer->organisation_short_code ?? 'ORG';
            $year = date('y');
            $lastPoNumber = ManualOrder::where('buyer_id', $userId)
                            ->orderBy('id', 'desc')
                            ->value('manual_po_number');
            $parts = explode('-', $lastPoNumber);
            $lastNumber = end($parts);
            $manualPoNumber = 'MO-' . $orgShortCode . '-' . $year . '-' . sprintf('%03d', $lastNumber+1);
            // Create the manual order
            $manualOrder = ManualOrder::create([
                'manual_po_number'       => $manualPoNumber,
                'vendor_id'              => $request->vendor_user_id,
                'buyer_id'               => $userId,
                'buyer_user_id'          => Auth::user()->id,
                'order_status'           => '1',
                'order_price_basis'      => $request->priceBasis,
                'order_payment_term'     => $request->paymentTerms,
                'order_delivery_period'  => $request->deliveryPeriod,
                'order_remarks'          => $request->remarks,
                'order_add_remarks'      => $request->additionalRemarks,
                'prepared_by'            => $userId,
                'approved_by'            => $userId,
            ]);

            // Store each product
            foreach ($request->qty as $index => $quantity) {
                $gstRate = Tax::find($request->gst[$index])?->tax ?? 0;
                $totalAmount = $quantity * $request->rate[$index] * (1 + ($gstRate / 100));

                ManualOrderProduct::create([
                    'manual_order_id'      => $manualOrder->id,
                    'product_id'           => Inventories::where('id', $request->inventory_id[$index])->value('product_id'),
                    'inventory_id'         => $request->inventory_id[$index],
                    'product_quantity'     => $quantity,
                    'product_price'        => $request->rate[$index],
                    'product_total_amount' => $totalAmount,
                    'product_gst'          => $request->gst[$index],
                ]);
            }

            DB::commit();
            //start send mail
            $this->sendEmail($manualPoNumber,$request);//pingki
            //end send mail

            return response()->json([
                'status' => '1',
                'message' => 'Manual PO generated successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => '2',
                'message' => 'Failed to generate Manual PO. ' . $e->getMessage(),
            ]);
        }
    }
    public function sendEmail($manualPoNumber, $request)//pingki
    {
        $vendor = Vendor::where('user_id', $request->vendor_user_id)
            ->with(['user', 'vendor_country', 'vendor_state', 'vendor_city'])
            ->first();

        $vendorName = $vendor->user->name;
        $vendorAddress = $vendor->registered_address;
        $buyername = Auth::user()->name;

        $order = [
            'order_number' => $manualPoNumber,
            'order_date' => date('d/m/Y'),
        ];

        $items = [];

        $alltotalAmount = 0;

        foreach ($request->qty as $index => $quantity) {
            $rate = $request->rate[$index];
            $inventory = Inventories::with(['product', 'uom'])->findOrFail($request->inventory_id[$index]);

            $productName = $inventory->product->product_name;
            $uomName = $inventory->uom->uom_name;


            $totalAmount = $quantity * $rate ;
            $alltotalAmount += $totalAmount;

            $items[] = [
                'product_name' => $productName,
                'quantity' => NumberFormatterHelper::formatQty($quantity,session('user_currency')['symbol'] ?? '₹'),
                'uom' => $uomName,
                'rate' => NumberFormatterHelper::formatCurrency($rate,session('user_currency')['symbol'] ?? '₹'),
                'total' => NumberFormatterHelper::formatCurrency($totalAmount,session('user_currency')['symbol'] ?? '₹')
            ];
        }

        $order['total_amount'] = NumberFormatterHelper::formatCurrency($alltotalAmount,session('user_currency')['symbol'] ?? '₹');

        Mail::to('pingkidas.prowebhill@gmail.com')->send(
            new ManualOrderConfirmationMail($vendorName, $buyername, $vendorAddress, $order, $items)
        );

        // return 'Email sent!';
    }

    //--------------------------------------------MANUAL PO REPORT-----------------------------------------------------------
    public function listdata(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->getFilteredQuery($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();
        $data = [];
        foreach ($inventories as $row) {
            $filteredProducts = $row->products;

            if ($request->search_product_name) {
                $filteredProducts = $filteredProducts->filter(function ($product) use ($request) {
                    return stripos($product->product->product_name ?? '', $request->search_product_name) !== false;
                });
            }

            $totalAmount = $filteredProducts->sum('product_total_amount');



            $data[] = [
                'manual_po_number' => '<a href="' . route('buyer.report.manualPO.orderDetails', $row->id) . '">' . $row->manual_po_number . '</a>',
                'order_date' => $row->created_at ? Carbon::parse($row->created_at)->format('d-m-Y') : '',
                'product_names' => TruncateWithTooltipHelper::wrapText($this->formatProductName($row, $request->search_product_name, $request->search_category_id)),
                'vendor_name' => optional($row->vendor)->name ?? '',
                'prepared_by' => optional($row->preparedBy)->name ?? '',
                'total_amount' => NumberFormatterHelper::formatCurrency($totalAmount,session('user_currency')['symbol'] ?? '₹'),
                'status' =>$row->order_status == 1 ? 'Confirmed' : 'Cancelled',
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_vendor_name',
                'search_order_no',
                'order_status',
                'from_date',
                'to_date',
            ]);

        $export = new ManualPoReportExport($filters,session('user_currency')['symbol'] ?? '₹');
        $fileName = 'Manual_PO_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }


    public function getFilteredQuery(Request $request)
    {
        if (session('branch_id') != $request->branch_id) {
                session(['branch_id' => $request->branch_id]);
            }

        $query = ManualOrder::with(['vendor', 'preparedBy', 'products.product','products.inventory.branch']);

        $query->when($request->buyer_id == Auth::user()->parent_id ?? Auth::user()->id, fn($q) => $q->where('buyer_id', Auth::user()->parent_id ?? Auth::user()->id))
        ->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $to   = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();

            $q->whereBetween('created_at', [$from, $to]);
        })
            ->when($request->search_product_name, fn($q, $val) =>
                $q->whereHas('products.product', fn($p) => $p->where('product_name', 'like', "%$val%"))
            )
            ->when($request->search_order_no, fn($q, $val) => $q->where('manual_po_number', 'like', "%$val%"))
            ->when($request->search_vendor_name, fn($q, $val) =>
                $q->whereHas('vendor', fn($v) => $v->where('name', 'like', "%$val%"))
            )
            ->when($request->search_category_id, function ($q, $val) {
                $cat_ids = InventoryController::getIdsByCategoryName($val);

                if (!empty($cat_ids)) {
                    $q->whereHas('products.product', function ($q2) use ($cat_ids) {
                        $q2->whereIn('category_id', $cat_ids);
                    });
                }
            })

            ->when($request->order_status, fn($q, $val) => $q->where('order_status', $val))
            ->when($request->branch_id, fn($q, $val) =>
            $q->whereHas('products.inventory.branch', fn($b) => $b->where('branch_id', $val)))
            ->orderBy('id', 'desc')->orderBy('created_at', 'desc');

        return $query;
    }


    public function formatProductName($order, $searchProductName = null, $searchCategoryName = null)
    {
        $productNames = $order->products
            ->filter(function ($product) use ($searchProductName, $searchCategoryName) {
                $matchesName = true;
                $matchesCategory = true;

                if ($searchProductName) {
                    $matchesName = stripos($product->product->product_name ?? '', $searchProductName) !== false;
                }

                if ($searchCategoryName) {
                    $searchCategoryId=InventoryController::getIdsByCategoryName($searchCategoryName);
                    $matchesCategory = in_array($product->product->category_id ?? null, $searchCategoryId);
                }

                return $matchesName && $matchesCategory;
            })
            ->pluck('product.product_name')
            ->filter()
            ->unique()
            ->sort()
            ->implode(', ');
        return e($productNames);
    }

    public function orderDetails(Request $request)//pingki
    {
        session(['page_title' => 'Order Details - Raprocure']);
        $order = ManualOrder::with('products','vendor')->find($request->id);

        if (!$order) {
            abort(404, 'Manual PO not found.');
        }

        return view('buyer.inventory.manualPoDetails', compact('order'));
    }
    //----------------------------------------------Manual PO Cancel order ---------------------------------------------------
    public function cancelManualOrder(Request $request)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json([
                'status' => 3,
                'message' => 'No Record Found'
            ]);
        }

        $buyerId = Auth::user()->parent_id ?? Auth::id();

        $poOrder = ManualOrder::select('id', 'manual_po_number', 'vendor_id')
            ->where('id', $orderId)
            ->where('buyer_user_id', $buyerId)
            ->where('order_status', 1)
            ->first();

        if (!$poOrder) {
            return response()->json([
                'status' => 3,
                'message' => 'No Record Found'
            ]);
        }

        $grnExists = GRN::where('po_number', $poOrder->manual_po_number)
            ->where('order_id', $poOrder->id)
            ->where('grn_type', 4)
            ->where('is_deleted', '!=', 1)
            ->exists();

        if ($grnExists) {
            return response()->json([
                'status' => 2,
                'message' => 'GRN Already Processed, Cannot delete the order!'
            ]);
        }

        DB::beginTransaction();

        try {
            ManualOrder::where('id', $poOrder->id)
                ->update([
                    'order_status' => 2,
                    'updated_at' => now()
                ]);

            $this->sendNotificationToVendor([
                'message_type' => 'order_cancelled',
                'user_id' => $poOrder->vendor_id,
                'rfq_no' => $poOrder->manual_po_number
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => 'Order Cancelled'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 2,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function sendNotificationToVendor($data)//pingki
    {
        // notification logic here
    }
    //-------------------------------------------------------------Download Manual Order----------------------------------
    public function download($id)
    {
        $order = ManualOrder::findOrFail($id);
        $order->load([
            'products.inventory.branch','vendor','vendor.vendor',
            'products.vendorProducts' => function ($query) use ($order) {
                $query->where('vendor_id', $order->vendor_id);
            }
        ]);

        // return view('buyer.inventory.downloadManualPO', compact('order'));
        $pdf = Pdf::loadView('buyer.inventory.downloadManualPO', compact('order'))
          ->setPaper('A4', 'portrait');

        return $pdf->download("Manual_Order.pdf");
    }

}
