<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\{GrnReportExport,PendingGrnReportExport,PendingGrnForStockReturnReportExport};
use App\Http\Controllers\Controller;
use App\Http\Controllers\Buyer\ManualPOController;
use App\Http\Controllers\Buyer\InventoryController;
use App\Models\{
    ManualOrder,Grn,ManualOrderProduct,ReturnStock,User,Rfq,Inventories,OrderVariant,Order,Indent,RfqProductVariant,Issued
};
use App\Helpers\{
    NumberFormatterHelper,TruncateWithTooltipHelper,PendingGrnUpdateBYrHelper,PendingGrnStockReturnUpdateBYrHelper
};
use App\Services\ExportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use Pdf;

class GrnController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    public function checkGrnEntry($inventoryId)
    {
        ManualPOController::userCurrency();
        $buyerId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

        $pendingManualOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'manual_order');
        $pendingRfqOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'rfq_order');

        $pendingManualOrderArray = $pendingManualOrder instanceof \Illuminate\Support\Collection ? $pendingManualOrder->toArray() : (is_array($pendingManualOrder) ? $pendingManualOrder : []);
        $pendingRfqOrderArray = $pendingRfqOrder instanceof \Illuminate\Support\Collection ? $pendingRfqOrder->toArray() : (is_array($pendingRfqOrder) ? $pendingRfqOrder : []);

        $pendingOrderArray = array_merge($pendingManualOrderArray, $pendingRfqOrderArray);

        $stockReturnOrder = $this->getPendingOrderDetails($buyerId, $inventoryId, 'stock_return');
        $stockReturnOrderArray = $stockReturnOrder instanceof \Illuminate\Support\Collection ? $stockReturnOrder->toArray() : (is_array($stockReturnOrder) ? $stockReturnOrder : []);

        return response()->json([
            'has_pending_order' => !empty($pendingOrderArray) || !empty($stockReturnOrderArray),
            'order_details' => $pendingOrderArray,
            'stock_return_details' => $stockReturnOrderArray,
            'inventoryId' => $inventoryId,
            'order' => !empty($pendingOrderArray),
            'stockReturn' => !empty($stockReturnOrderArray),
        ]);
    }

    private function getPendingOrderDetails($buyerId, $inventoryId, $orderType)
    {
        if($orderType=='stock_return'){
            $pendingOrders = ReturnStock::where('buyer_id', $buyerId)
                ->where('inventory_id', $inventoryId)
                ->where('stock_return_type', '1')
                ->get();

            if ($pendingOrders->isEmpty()) {
                return [];
            }
            return $pendingOrders->flatMap(function ($stockReturn) use ($inventoryId, $buyerId,$orderType) {
                $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                    ->where('company_id', $buyerId)
                    ->where('stock_id', $stockReturn->id)
                    ->sum('grn_qty');

                if ((float)($maxGrnQty ?? 0) < (float)$stockReturn->qty) {
                     return [[
                        'stock_return_id' => $stockReturn->id,
                        'stock_return_for' => $stockReturn->stock_return_for,
                        'order_type' => $orderType,
                        'stock_no' => $stockReturn->stock_no,
                        'remarks' => $stockReturn->remarks,
                        'updated_at' => Carbon::parse($stockReturn->updated_at)->format('d-m-Y'),
                        'qty' => $stockReturn->qty,
                        'stock_vendor_name' => $stockReturn->stock_vendor_name ?? '-',
                        'grn_entered' =>  NumberFormatterHelper::formatQty($maxGrnQty,session('user_currency')['symbol'] ?? '₹' ),
                    ]];
                }
                return [];
            });

        }else if($orderType=='rfq_order'){
            $pendingOrders = Rfq::with([
                    'rfqProductVariants' => function ($query) use ($inventoryId) {
                        $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                    },
                    'orders' => function ($query) {
                        $query->where('order_status', 1);
                    },
                    'orders.order_variants',
                    'orders.vendor'
                ])
                ->where('record_type', 2)                
                ->whereHas('rfqProductVariants', function ($query) use ($inventoryId) {
                    $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                })
                ->get();

            if ($pendingOrders->isEmpty()) {
                return [];
            }

            $result = [];

            foreach ($pendingOrders as $rfq) {
                foreach ($rfq->orders as $order) {
                    foreach ($order->order_variants as $variant) {
                        $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                            ->where('company_id', $buyerId)
                            ->where('order_id', $order->id)
                            ->sum('grn_qty');
                        $grn_buyer_rate=Grn::where('inventory_id', $inventoryId)
                            ->where('company_id', $buyerId)
                            ->where('order_id', $order->id)
                            ->MAX('grn_buyer_rate');
                        if ((float)($maxGrnQty ?? 0) < (float) $variant->order_quantity) {
                            $currency = $this->getVendorCurrency($order->vendor_id);

                            $result[] = [
                                'id' => $order->id,
                                'order_type' => $orderType,
                                'order_number' => $order->po_number,
                                'rfq_number' => $rfq->rfq_id,
                                'order_date' => $order->created_at?->format('d-m-Y'),
                                'order_quantity' => $variant->order_quantity,
                                'vendor_name' => $order->vendor->legal_name ?? 'N/A',
                                'grn_entered' => NumberFormatterHelper::formatQty($maxGrnQty, session('user_currency')['symbol'] ?? '₹'),
                                'ratewithcurrency' => NumberFormatterHelper::formatCurrency($variant->order_price, $currency),
                                'rate_in_local_currency' => $this->RequiredLocalCurrencyOrNot($currency, $orderType),
                                'grn_buyer_rate' => ($grn_buyer_rate > 0)? NumberFormatterHelper::formatQty($grn_buyer_rate, session('user_currency')['symbol'] ?? '₹'): 0,

                                'rate' => $variant->order_price,
                                'grn_quantity' => $variant->grn_quantity ?? 0,
                                'invoice_number' => $order->invoice_number ?? 'N/A',
                                'vehicle_lr_number' => $variant->vehicle_lr_number ?? 'N/A',
                                'gross_weight' => $variant->gross_weight ?? 'N/A',
                                'gst' => $variant->product_gst ?? 0,
                                'freight_charges' => $variant->freight_charges ?? 0,
                                'approved_by' => $order->approved_by ?? 'N/A',
                                'baseManualPoUrl' => route('buyer.report.manualPO.orderDetails', ['id' => '__ID__']),
                            ];
                        }
                    }
                }
            }

            return $result;

        }else{
            $pendingOrders = ManualOrder::where('buyer_id', $buyerId)
                        ->where('order_status', 1)
                        ->whereHas('products', function ($query) use ($inventoryId) {
                            $query->where('inventory_id', $inventoryId);
                        })
                        ->with([
                            'products' => function ($query) use ($inventoryId) {
                                $query->where('inventory_id', $inventoryId);
                            },
                            'vendor'
                        ])
                        ->get();
            if ($pendingOrders->isEmpty()) {
                return [];
            }
            return $pendingOrders->flatMap(function ($order) use ($inventoryId, $orderType, $buyerId) {
                return $order->products->map(function ($product) use ($order, $inventoryId, $orderType, $buyerId) {
                    $maxGrnQty = Grn::where('inventory_id', $inventoryId)
                        ->where('company_id', $buyerId)
                        ->where('order_id', $product->manual_order_id)
                        ->sum('grn_qty');

                    if ((float)($maxGrnQty ?? 0) < (float)$product->product_quantity) {
                        $currency = session('user_currency')['symbol'] ?? '₹';
                        return [
                            'id' => $product->manual_order_id,
                            'order_type' => $orderType,
                            'order_number' => $order->manual_po_number,
                            'rfq_number' => $order->rfq_number,
                            'order_date' => $order->created_at->format('d-m-Y'),
                            'order_quantity' => $product->product_quantity,
                            'vendor_name' => $order->vendor->name ?? 'N/A',
                            'grn_entered' => NumberFormatterHelper::formatQty($maxGrnQty,session('user_currency')['symbol'] ?? '₹' ),
                            'ratewithcurrency' => NumberFormatterHelper::formatCurrency($product->product_price, $currency),
                            'rate' => $product->product_price,
                            'rate_in_local_currency' => $this->RequiredLocalCurrencyOrNot($currency, $orderType),
                            'grn_quantity' => $product->grn_quantity ?? 0,
                            'invoice_number' => $order->invoice_number ?? 'N/A',
                            'vehicle_lr_number' => $product->vehicle_lr_number ?? 'N/A',
                            'gross_weight' => $product->gross_weight ?? 'N/A',
                            'gst' => $product->product_gst ?? 0,
                            'freight_charges' => $product->freight_charges ?? 0,
                            'approved_by' => $order->approved_by ?? 'N/A',
                            'baseManualPoUrl' => route('buyer.report.manualPO.orderDetails', ['id' => '__ID__']),
                        ];
                    }
                    return null;
                })->filter();
            });
        }
    }
    private function getVendorCurrency($vendorId)
    {
        $user = User::with('currencyDetails')->find($vendorId);

        if ($user && $user->currencyDetails) {
            $symbol = $user->currencyDetails->currency_symbol;
        } else {
            $symbol = '₹';
        }
        return $symbol;
    }
    private function RequiredLocalCurrencyOrNot($vendorCurrency,$orderType)
    {
        $currency = session('user_currency')['symbol'] ?? '₹';
        if(($orderType!='manual_order')){
            if($vendorCurrency===$currency){
                return '0';
            }
            else{
                return '1';
            }
        }else{
            return '0';
        }

    }

    //-------------------------------------Store GRN---------------------------------------------------------------

    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $this->validateRequest($request);

        $companyId = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $userId = Auth::user()->id;

        $inventoryId = $request->inventory_id;

        $grnQtys = collect($request->grn_qty)->filter(fn($qty) => is_numeric($qty));
        $grnStockReturnQtys = collect($request->grn_stock_return_qty)->filter(fn($qty) => is_numeric($qty));

        $allQtys = $grnQtys->concat($grnStockReturnQtys)->values();

        $this->checkEmptyGrnOrZeroValueGrn($allQtys);

        $orderIds = collect($request->order_id)->filter(fn($id) => is_numeric($id));
        $stockIds = collect($request->stock_return_id)->filter(fn($id) => is_numeric($id));
        $grnTypes = collect($request->grn_type)->filter(fn($grn_type) => is_numeric($grn_type));
        $stockReturnGrnTypes = collect($request->stock_return_grn_type)->filter(fn($grn_type) => is_numeric($grn_type));

        //order grn insert
        foreach ($grnQtys as $index => $qty) {
            if($grnTypes[$index]=='4'){
                $this->checkMaxQtyForManualOrderGrn(floatval($qty), $inventoryId, $orderIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }
                Grn::insert($validData);
            }
            if($grnTypes[$index]=='1'){
                $this->checkMaxQtyForRfqGrn(floatval($qty), $inventoryId, $orderIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForOrderRow($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }
                // dd($validData);
                Grn::insert($validData);
                $remainingGrnQty = floatval($qty);
                $indents = Indent::where('inventory_id', $inventoryId)->where('inv_status', 1)->where('is_deleted', 2)->where('closed_indent', 2)->orderBy('id', 'asc')->get();

                foreach ($indents as $indent) {
                    $remaining = $indent->indent_qty - $indent->grn_qty;
                    if ($remainingGrnQty <= 0) {
                        break;
                    }
                    if ($remainingGrnQty >= $remaining) {
                        $indent->update([
                            'grn_qty' => $indent->indent_qty,
                            'closed_indent' => 1
                        ]);
                        $remainingGrnQty -= $remaining;
                    } else {
                        $indent->update([
                            'grn_qty' => $indent->grn_qty + $remainingGrnQty
                        ]);
                        $remainingGrnQty = 0;
                        break;
                    }
                }
                $indentQty = Indent::where('inventory_id', $inventoryId)->where('is_deleted', 2)->where('closed_indent', 2)->sum('indent_qty');
                $inventoryController = app(InventoryController::class);
                $totalGrnQty = $inventoryController->getGrnData($inventoryId)['grn_qty'][$inventoryId] ?? 0;

                $tolerance = $indentQty * 0.02;
                $requiredQty = $indentQty - $tolerance;

                if ($totalGrnQty >= $requiredQty) {
                    Indent::where('inventory_id', $inventoryId)->update(['inv_status' => 2, 'closed_indent' => 1]);
                    RfqProductVariant::where('inventory_id', $inventoryId)->update(['inventory_status' => 2]);
                    Grn::where('inventory_id', $inventoryId)->update(['inv_status' => 2]);
                    Issued::where('inventory_id', $inventoryId)->update(['inv_status' => 2]);
                    Inventories::where('id', $inventoryId)->update(['is_indent' => 2]);
                }
            }
        }

        //stock return grn insert
        foreach ($grnStockReturnQtys as $index => $qty) {
            if($stockReturnGrnTypes[$index]=='3'){
                $this->checkMaxQtyForStockReturnGrn(floatval($qty), $inventoryId, $stockIds[$index] ?? null);
                $nextGrnNumber = Grn::getNextGrnNumber($companyId);
                $validData = $this->buildValidDataForStockReturn($request, collect([$index => $qty]), $inventoryId, $companyId, $userId, $nextGrnNumber);

                if (empty($validData)) {
                    return $this->errorResponse('grn_qty', 'No valid GRN quantities provided.');
                }
                
                Grn::insert($validData);
            }
        }


        return response()->json([
            'status' => true,
            'message' => 'Valid GRN submission.',
        ]);
    }
    protected function errorResponse($field, $message)
    {
        return response()->json([
            'status' => false,
            'errors' => [$field => [$message]],
        ], 422);
    }
    protected function validateRequest(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventories,id',

            // GRN set
            'grn_qty'     => 'sometimes|array',
            'grn_qty.*'   => ['nullable', 'string', 'max:20', 'min:0.01', new NoSpecialCharacters(false)],

            'order_qty'   => 'sometimes|array',
            'grn_entered' => 'sometimes|array',

            // Common optional fields
            'invoice_number.*'    => ['nullable', 'string', 'max:50', new NoSpecialCharacters(false)],
            'vehicle_lr_number.*' => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'gross_weight.*'      => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'gst.*'               => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'freight_charges.*'   => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'approved_by.*'       => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],

            // Stock Return set
            'grn_stock_return_qty'     => 'sometimes|array',
            'grn_stock_return_qty.*'   => ['nullable', 'string', 'max:20', 'min:0.01', new NoSpecialCharacters(false)],

            'stock_return_qty'         => 'sometimes|array',
            'stock_return_grn_entered' => 'sometimes|array',
        ]);

        // Manual validation: At least one group must be present
        $hasGrnSet = $request->filled('grn_qty') && $request->filled('order_qty') && $request->filled('grn_entered');
        $hasStockReturnSet = $request->filled('grn_stock_return_qty') && $request->filled('stock_return_qty') && $request->filled('stock_return_grn_entered');

        if (!($hasGrnSet || $hasStockReturnSet)) {
            return back()->withErrors([
                'error' => 'At least one of GRN or Stock Return data must be provided.'
            ])->withInput();
        }
    }

    protected function checkEmptyGrnOrZeroValueGrn($Qtys)
    {
        $Qtys = collect($Qtys);

        if ($Qtys->isEmpty()) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['At least one GRN Quantity is required.']],
            ], 422));
        }

        if ($Qtys->contains(fn($qty) => floatval($qty) <= 0)) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity must be greater than 0.']],
            ], 422));
        }

        return true;
    }
    protected function checkMaxQtyForRfqGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::with(['order.order_variants','order'])
            ->where('inventory_id', $inventoryId)
            ->where('order_id', $orderId)
            ->get();
        if ($grns->isEmpty()) {
            $totalGrnEntered = 0;
        } else {
            $totalGrnEntered = $grns->sum('grn_qty');
        }

        $first = $grns->first();
        
        if ($first) {
            $orderQty = optional($first->order->order_variants->first())->order_quantity ?? 0;
        } else {
            $productId = Inventories::where('id', $inventoryId)->value('product_id');
            $po_number = Order::where('id', $orderId)->value('po_number');
            $RfqOrderProduct = OrderVariant::where('product_id', $productId)
                ->where('po_number', $po_number)
                ->first();

            $orderQty = $RfqOrderProduct?->order_quantity ?? 0;
        }
        
        $maxOrderQty = $orderQty * 1.02;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 2);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function checkMaxQtyForManualOrderGrn($grnQtys, $inventoryId, $orderId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::with(['manualOrderProduct'])
            ->where('inventory_id', $inventoryId)
            ->where('order_id', $orderId)
            ->get();
        if ($grns->isEmpty()) {
            $totalGrnEntered = 0;
        } else {
            $totalGrnEntered = $grns->sum('grn_qty');
        }

        $first = $grns->first();
        if ($first) {
            $orderQty = optional($first->manualOrderProduct)->product_quantity ?? 0;
        } else {
            $manualOrderProduct = ManualOrderProduct::where('inventory_id', $inventoryId)
                ->where('manual_order_id', $orderId)
                ->first();

            $orderQty = $manualOrderProduct?->product_quantity ?? 0;
        }
        $maxOrderQty = $orderQty * 1.02;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 2);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function checkMaxQtyForStockReturnGrn($grnQtys, $inventoryId, $stockId)
    {
        $grnQtys = collect($grnQtys);
        $grns = Grn::where('inventory_id', $inventoryId)
            ->where('stock_id', $stockId)
            ->get();

        $totalGrnEntered = $grns->isEmpty() ? 0 : $grns->sum('grn_qty');
        $orderQty = ReturnStock::where('inventory_id', $inventoryId)->where('id', $stockId)->value('qty')?? 0;
        $maxOrderQty = $orderQty * 1.02;
        $maxGrnQty =  round($maxOrderQty - $totalGrnEntered, 2);
        $exceedsMaxQty = $grnQtys->filter(function($qty) use ($maxGrnQty) {
            return floatval($qty) > floatval($maxGrnQty);
        })->isNotEmpty();
        if ($exceedsMaxQty) {
            abort(response()->json([
                'status' => false,
                'errors' => ['grn_qty' => ['GRN Quantity exceeds allowed 2% over Order Qty. Max GRN Qty: ' . $maxGrnQty]],
            ], 422));
        }
    }
    protected function buildValidDataForOrderRow($request, $grnQtys, $inventoryId, $companyId, $userId, $nextGrnNumber)
    {
        $validData = [];

        foreach ($grnQtys as $index => $qty) {
            $grnQty = number_format(floatval($qty), 2, '.', '');
            $orderQty = number_format(floatval($request->order_qty[$index] ?? 0), 2, '.', '');
            $enteredQty = number_format(floatval($request->grn_entered[$index] ?? 0), 2, '.', '');
            $remainingQty = number_format(($orderQty * 1.02) - $enteredQty, 2, '.', '');
            $orderId = $request->order_id[$index];
            $grnType = $request->grn_type[$index];

            if ($grnType == 1) {
                $orderStatus = Order::where('id', $orderId)->value('order_status');
            } elseif ($grnType == 4) {
                $orderStatus = ManualOrder::where('id', $orderId)->value('order_status');
            } else {
                abort(response()->json([
                    'status' => false,
                    'message' => 'Invalid GRN type.',
                ], 422));
            }

            if ($orderStatus == 2) {
                abort(response()->json([
                    'status' => false,
                    'message' => 'Order is already Cancelled. Please refresh the GRN details form.',
                ], 422));
            }


            // if ($grnQty > 0 && $grnQty <= $remainingQty) {
            if (bccomp($grnQty, '0', 2) === 1 && bccomp($grnQty, $remainingQty, 2) <= 0) {
                $validData[] = [
                    'inventory_id'     => $inventoryId,
                    'grn_qty'          => $grnQty,
                    'order_id'         => $orderId,
                    'po_number'        => $request->po_number[$index],
                    'company_id'       => $companyId,
                    'updated_by'       => $userId,
                    'grn_no'           => $nextGrnNumber++,
                    'order_qty'        => $orderQty,
                    'rate'             => $request->rate[$index],
                    'grn_buyer_rate'   => $request->rate_in_local_currency[$index]?? 0,
                    'grn_type'         => $request->grn_type[$index],
                    'vendor_name'      => $request->vendor_name[$index],
                    'vendor_invoice_number'   => $request->invoice_number[$index] ?? null,                    
                    'bill_date'         => $request->bill_date[$index] ?? null,
                    'transporter_name'   => $request->transporter_name[$index] ?? null,
                    'vehicle_no_lr_no'        => $request->vehicle_lr_number[$index] ?? null,
                    'gross_wt'                => $request->gross_weight[$index] ?? null,
                    'gst_no'                  => $request->gst[$index] ?? null,
                    'frieght_other_charges'   => $request->freight_charges[$index] ?? null,
                    'approved_by'             => $request->approved_by[$index] ?? null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }
        return $validData;
    }
    protected function buildValidDataForStockReturn($request, $grnStockReturnQtys, $inventoryId, $companyId, $userId, $nextGrnNumber)
    {
        $validData = [];

        foreach ($grnStockReturnQtys as $index => $qty) {
            $grnQty = number_format(floatval($qty), 2, '.', '');
            $orderQty = number_format(floatval($request->stock_return_qty[$index] ?? 0), 2, '.', '');
            $enteredQty = number_format(floatval($request->stock_return_grn_entered[$index] ?? 0), 2, '.', '');

            $remainingQty = number_format(($orderQty * 1.02) - $enteredQty, 2, '.', '');
            $stock_id = $request->stock_return_id[$index];
            $stock_return_for = $request->stock_return_for[$index];
            
            if (bccomp($grnQty, '0', 2) === 1 && bccomp($grnQty, $remainingQty, 2) <= 0) {
                $validData[] = [
                    'inventory_id'     => $inventoryId,
                    'grn_qty'          => $grnQty,
                    'order_id'         => '0',
                    'po_number'        => '',
                    'company_id'       => $companyId,
                    'stock_id'         => $stock_id,
                    'stock_return_for' => $stock_return_for,
                    'updated_by'       => $userId,
                    'grn_no'           => $nextGrnNumber,
                    'grn_type'         => $request->stock_return_grn_type[$index],
                    'vendor_name'      => $request->stock_vendor_name[$index],
                    'vendor_invoice_number'   => $request->stock_invoice_number[$index] ?? null,
                    'bill_date'         => $request->stock_bill_date[$index] ?? null,
                    'transporter_name'   => $request->stock_transporter_name[$index] ?? null,
                    'vehicle_no_lr_no'        => $request->stock_vehicle_lr_number[$index] ?? null,
                    'gross_wt'                => $request->stock_gross_weight[$index] ?? null,
                    'gst_no'                  => $request->stock_gst[$index] ?? null,
                    'frieght_other_charges'   => $request->stock_freight_charges[$index] ?? null,
                    'approved_by'             => $request->stock_approved_by[$index] ?? null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        return $validData;
    }
    //-------------------------------------GRN REPORT TABLE & EXCEL----------------------------------------------------------
    public function grnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredQuery($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        $grns = collect($paginated->items());

        $data = $grns->map(function ($grn) {
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            return [
                'grn_no' => $grn->grn_no,
                'product' => optional($grn->inventory->product)->product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText($grn->inventory->specification),
                'size' => TruncateWithTooltipHelper::wrapText($grn->inventory->size),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($grn->inventory->inventory_grouping),
                'vendor_name' => TruncateWithTooltipHelper::wrapText($grn->vendor_name??''),
                'vendor_invoice_no' => TruncateWithTooltipHelper::wrapText($grn->vendor_invoice_number),
                'vehicle_no_lr_no' => TruncateWithTooltipHelper::wrapText($grn->vehicle_no_lr_no),
                'gross_wt' => TruncateWithTooltipHelper::wrapText($grn->gross_wt),
                'gst_no' => TruncateWithTooltipHelper::wrapText($grn->gst_no),
                'frieght_other_charges' => TruncateWithTooltipHelper::wrapText($grn->frieght_other_charges),
                'added_by' => TruncateWithTooltipHelper::wrapText(optional($grn->updatedBy)->name),
                'added_date' => $grn->updated_at ? Carbon::parse($grn->updated_at)->format('d-m-Y') : '',
                'grn_qty' =>'<span class="grn-entry-details" style="cursor:pointer;color:blue;" data-id="'.$grn->id.'" >'.NumberFormatterHelper::formatQty($grn->grn_qty,$currencySymbol) .'</span>',
                'uom' => $grn->inventory->uom->uom_name ?? '',
                'amount' => NumberFormatterHelper::formatCurrency($grn->order_rate*$grn->grn_qty,$currencySymbol),
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function exportGrnReport(Request $request)
    {
        if ($request->ajax()) {
            ini_set('memory_limit', '2048M');
            set_time_limit(3000);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $filters = $request->only([
                    'branch_id',
                    'search_product_name',
                    'search_category_id',
                    'from_date',
                    'to_date',
                ]);

            $export = new GrnReportExport($filters, $currencySymbol);
            $fileName = 'Grn_Report_' . now()->format('d-m-Y') . '.xlsx';

            $response = $this->exportService->storeAndDownload($export, $fileName);

            return response()->json($response);
        }
    }
    public function getFilteredQuery(Request $request)
    {
        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query = Grn::with(['inventory', 'company', 'inventory.product','updatedBy','manualOrder.vendor','manualOrderProduct']);

        $query->when(
            $request->company_id == (Auth::user()->parent_id ?? Auth::user()->id),
            fn($q) => $q->where('company_id', Auth::user()->parent_id ?? Auth::user()->id)
        );

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$from, $to]);
        });

        $query->when($request->search_product_name, function ($q, $val) {
            $q->whereHas('inventory.product', fn($p) => $p->where('product_name', 'like', "%$val%"));
        });

        $query->when($request->search_category_id, function ($q, $val) {
            $cat_ids = InventoryController::getIdsByCategoryName($val);
            if ($cat_ids) {
                $q->whereHas('inventory.product', fn($p) => $p->whereIn('category_id', $cat_ids));
            }
        });

        $query->when($request->branch_id, function ($q, $val) {
            $q->whereHas('inventory.branch', fn($b) => $b->where('branch_id', $val));
        });

        return $query->orderByDesc('id')->orderByDesc('updated_at');
    }

    //---------------------------------IN GRN REPORT FETCH DETAILS AND EDIT DETAILS--------------------------------------------
    public function fetchGrnRowdata(Request $request)
    {
        $grn = Grn::with(['inventory', 'manualOrder', 'manualOrderProduct', 'stock', 'order'])->find($request->id);

        if (!$grn) {
            return response()->json(['html' => '<p class="text-danger">GRN not found.</p>']);
        }

        // Base data
        $data = [
            'id' => $grn->id,
            'grn_qty' => NumberFormatterHelper::formatQty($grn->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
            'vendor_invoice_no' => $grn->vendor_invoice_number ?? '',
            'bill_date' => $grn->bill_date ?? '',
            'transporter_name' => $grn->transporter_name ?? '',
            'gross_wt' => $grn->gross_wt ?? '',
            'vehicle_no_lr_no' => $grn->vehicle_no_lr_no ?? '',
            'gst_no' => $grn->gst_no ?? '',
            'frieght_other_charges' => $grn->frieght_other_charges ?? '',
            'approved_by' => $grn->approved_by ?? '',
        ];

        // Type-specific data
        if ($grn->grn_type == 3 && $grn->stock) {
            $data += [
                'stock_return_no' => $grn->stock->stock_no ?? '',
                'stock_return_date' => $grn->stock->updated_at ? Carbon::parse($grn->stock->updated_at)->format('d-m-Y') : '',
                'stock_return_qty' => $grn->stock->qty ?? '',
                'vendor_name' => $grn->stock->vendor_name ?? '',
            ];
        } else {
            $data += [
                'order_no' => $grn->po_number ?? '',
                'rfq_no' => $grn->rfq_id ?? '-',
                'order_date' => $grn->created_at ? Carbon::parse($grn->created_at)->format('d-m-Y') : '',
                'order_qty' => $grn->order_qty ?? '',
                'vendor_name' => $grn->vendor_name ?? '',
                'grn_no' => $grn->grn_no ?? '',
                'grn_date' => $grn->updated_at ? Carbon::parse($grn->updated_at)->format('d-m-Y') : '',
            ];
        }

        return response()->json($data);
    }

    public function editGrnRowdata(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $request->validate([
            'id' => 'required|exists:grns,id',
            'invoice_number'     => ['nullable', 'string', 'max:50', new NoSpecialCharacters(false)],
            'bill_date'          => ['nullable', 'string', 'max:50', new NoSpecialCharacters(false)],
            'transporter_name'    => ['nullable', 'string', 'max:50', new NoSpecialCharacters(false)],
            'vehicle_lr_number'  => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'gross_weight'       => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'gst'                => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'freight_charges'    => ['nullable', 'string', 'max:20', new NoSpecialCharacters(false)],
            'approved_by'        => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
        ]);

        try {
            $grn = Grn::findOrFail($request->id);

            $grn->vendor_invoice_number = $request->invoice_number;
            $grn->bill_date = $request->bill_date;
            $grn->transporter_name = $request->transporter_name;
            $grn->vehicle_no_lr_no = $request->vehicle_lr_number;
            $grn->gross_wt = $request->gross_weight;
            $grn->gst_no = $request->gst;
            $grn->frieght_other_charges = $request->freight_charges;
            $grn->approved_by = $request->approved_by;
            $grn->save();

            return response()->json(['status' => 'success', 'message' => 'GRN updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to update GRN.']);
        }
    }
    public function downloadGrnRowdata($id)
    {
        $buyer = User::find(Auth::user()->parent_id ?? Auth::id());
        $grnModel = Grn::with(['inventory.product', 'manualOrder', 'manualOrderProduct', 'stock', 'order'])->find($id);

        if (!$grnModel) {
            abort(404, 'GRN not found');
        }

        $grn = [
            'buyer_name' => $buyer?->name ?? 'N/A',
            'grn_no' => $grnModel->grn_no,
            'order_no' => $grnModel->po_number,
            'order_date' => (!empty($grnModel->created_at) && strtotime($grnModel->created_at)) 
                ? Carbon::parse($grnModel->created_at)->format('d-m-Y') 
                : '',
            'vendor' => $grnModel->vendor_name ?? '',
            'product' => $grnModel->inventory->product->product_name ?? '',
            'size' => e($grnModel->inventory->size ?? ''),
            'specification' => e($grnModel->inventory->specification ?? ''),
            'grn_qty' => NumberFormatterHelper::formatQty($grnModel->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
            'product_order_qty' => NumberFormatterHelper::formatQty(
                ($grnModel->grn_type == 3 && $grnModel->stock) 
                    ? $grnModel->stock->qty 
                    : ($grnModel->order_qty ?? ''),
                session('user_currency')['symbol'] ?? '₹'
            ) ?? '',
            'bill_date' => (!empty($grnModel->bill_date) && strtotime($grnModel->bill_date)) 
                ? Carbon::parse($grnModel->bill_date)->format('d-m-Y') 
                : '',
            'rate' => NumberFormatterHelper::formatCurrency($grnModel->order_rate, session('user_currency')['symbol'] ?? '₹') ?? '',
            'amount' => NumberFormatterHelper::formatCurrency($grnModel->order_rate * $grnModel->grn_qty, session('user_currency')['symbol'] ?? '₹') ?? '',
            'vendor_invoice_no' => $grnModel->vendor_invoice_number ?? '',
            'transporter_name' => $grnModel->transporter_name ?? '',
            'gross_wt' => $grnModel->gross_wt ?? '',
            'vehicle_no_lr_no' => $grnModel->vehicle_no_lr_no ?? '',
            'gst_no' => $grnModel->gst_no ?? '',
            'frieght_other_charges' => $grnModel->frieght_other_charges ?? '',
            'approved_by' => $grnModel->approved_by ?? '',
        ];

        $pdf = Pdf::loadView('buyer.report.downloadGrnPdf', compact('grn'))
            ->setOptions([
                'defaultFont'     => 'DejaVu Sans',
                'isRemoteEnabled' => true,
            ]);

        return $pdf->download("GRN No-{$id} details.pdf");
    }


    //--------------------------------------------PENDING GRN REPORT--------------------------------------------------------------
    public function pendingGrnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredPendingGrnData($request);

        $allGrns = collect();
        $filteredQuery->chunk(100, function ($chunk) use (&$allGrns) {
            $allGrns = $allGrns->merge($chunk);
        });

        $data = $this->getFormatPendingGrnData($allGrns);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginatedData = array_slice($data->all(), ($page - 1) * $perPage, $perPage);
        $paginated = new LengthAwarePaginator(
            $paginatedData,
            count($data),
            $perPage,
            $page
        );
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);

    }

    public function exportPendingGrnReport(Request $request)
    {
        if ($request->ajax()) {
            ini_set('memory_limit', '2048M');
            set_time_limit(3000);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $filters = $request->only([
                    'branch_id',
                    'search_product_name',
                    'search_category_id',
                ]);

            $export = new PendingGrnReportExport($filters, $currencySymbol);
            $fileName = 'Pending_Grn_Report_' . now()->format('d-m-Y') . '.xlsx';

            $response = $this->exportService->storeAndDownload($export, $fileName);

            return response()->json($response);
        }
    }
    public function getFilteredPendingGrnData(Request $request)
    {
        $companyId = Auth::user()->parent_id ?? Auth::user()->id;

        $query = Grn::with([
            'inventory',
            'manualOrder',
            'manualOrderProduct',
            'updater',
            'inventory.branch',
            'inventory.product',
            'order',
            'order.order_variants'
        ])
        ->where('company_id', $companyId)
        ->select('inventory_id', 'order_id', 'grn_type',DB::raw('MAX(id) as latest_id'), DB::raw('MAX(updated_at) as last_updated_at'), DB::raw('sum(grn_qty) as totalGrnQty'));


        // Filter by product name
        $query->when($request->search_product_name, function ($q, $searchProductName) {
            $q->whereHas('inventory.product', function ($p) use ($searchProductName) {
                $p->where('product_name', 'like', '%' . $searchProductName . '%');
            });
        });

        // Filter by category ID (name)
        $query->when($request->search_category_id, function ($q, $searchCategoryId) {
            $catIds = InventoryController::getIdsByCategoryName($searchCategoryId);
            if (!empty($catIds)) {
                $q->whereHas('inventory.product', function ($p) use ($catIds) {
                    $p->whereIn('category_id', $catIds);
                });
            }
        });

        // Update session branch_id
        if (session('branch_id') !== $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        // Filter by branch
        $query->when($request->branch_id, function ($q, $branchId) {
            $q->whereHas('inventory.branch', function ($b) use ($branchId) {
                $b->where('branch_id', $branchId);
            });
        });

        return $query->groupBy('inventory_id', 'order_id','grn_type')
                    ->orderByRaw('MAX(id) DESC')
                    ->orderByRaw('MAX(updated_at) DESC')
                    ;
    }
    private function getFormatPendingGrnData($grns)
    {
        $updatedByMap = PendingGrnUpdateBYrHelper::getUpdatedByMap($grns);

        return $grns
            ->unique(fn($item) => $item->inventory_id . '-' . $item->order_id)
            ->filter(function ($item) {
                $totalGrnQty = round($item->totalGrnQty, 2);
                $orderQty = round($item->order_qty, 2);
                $pendingGrnQty = round($orderQty - $totalGrnQty, 2);

                return $totalGrnQty < $orderQty && $totalGrnQty > 0 && $pendingGrnQty > 0;
            })
            ->map(function ($item) use ($updatedByMap) {
                $totalGrnQty = round($item->totalGrnQty, 2);
                $orderQty = round($item->order_qty, 2);
                $pendingGrnQty = round($orderQty - $totalGrnQty, 2);

                $key = $item->inventory_id . '-' . $item->order_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
                $updatedById = $updatedByMap[$key] ?? null;
                $addedByName = $updatedById ? User::find($updatedById)->name : '';

                return [
                    'serial_number' => null,
                    'branch_name' => $item->inventory->branch->name ?? '',
                    'order_number' => $item->po_number,
                    'order_date' => optional($item->created_at)->format('d-m-Y'),
                    'product_name' => $item->inventory->product->product_name ?? '',
                    'vendor_name' => $item->vendor_name ?? '',
                    'specification' => TruncateWithTooltipHelper::wrapText($item->inventory->specification),
                    'size' => TruncateWithTooltipHelper::wrapText($item->inventory->size),
                    'inventory_grouping' => TruncateWithTooltipHelper::wrapText($item->inventory->inventory_grouping),
                    'added_by' => $addedByName,
                    'added_date' => Carbon::parse($item->last_updated_at)->format('d-m-Y'),
                    'uom' => $item->inventory->uom->uom_name ?? '',
                    'order_quantity' => NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
                    'total_grn_quantity' => NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹'),
                    'pending_grn_quantity' => NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
                ];
            })
            ->values()
            ->map(function ($item, $index) {
                $item['serial_number'] = $index + 1;
                return $item;
            });
    }
    //---------------------------------Pending GRN For STock Return-------------------------------------------------
    //start pingki
    public function pendingGrnStockReturnReportlistdata(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->getFilteredPendingGrnStockReturnData($request);

        $allGrns = collect();
        $filteredQuery->chunk(100, function ($chunk) use (&$allGrns) {
            $allGrns = $allGrns->merge($chunk);
        });

        $data = $this->getFormatPendingGrnStockReturnData($allGrns);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginatedData = array_slice($data->all(), ($page - 1) * $perPage, $perPage);
        $paginated = new LengthAwarePaginator(
            $paginatedData,
            count($data),
            $perPage,
            $page
        );
        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);

    }

    public function exportPendingGrnStockReturnReport(Request $request)
    {
        if ($request->ajax()) {
            ini_set('memory_limit', '2048M');
            set_time_limit(3000);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $filters = $request->only([
                    'branch_id',
                    'search_product_name',
                    'search_category_id',
                    'from_date',
                    'to_date',
                ]);

            $export = new PendingGrnForStockReturnReportExport($filters, $currencySymbol);
            $fileName = 'Pending_Grn_For_Stock_Return_Report_' . now()->format('d-m-Y') . '.xlsx';

            $response = $this->exportService->storeAndDownload($export, $fileName);

            return response()->json($response);
        }
    }
    public function getFilteredPendingGrnStockReturnData(Request $request)
    {
        $buyerId = Auth::user()->parent_id ?? Auth::user()->id;

        $query = Grn::with([
            'inventory',
            'stock',
            'updater',
            'inventory.branch',
            'inventory.product',
        ])
        ->where('company_id', $buyerId)
        ->where('grn_type', '3')
        ->select('inventory_id', 'stock_id', 'grn_type', DB::raw('GROUP_CONCAT(DISTINCT grn_no ORDER BY id ASC SEPARATOR ", ") as grn_no'), DB::raw('max(updated_at) as last_updated_at'), DB::raw('sum(grn_qty) as totalGrnQty'));


        // Filter by product name
        $query->when($request->search_product_name, function ($q, $searchProductName) {
            $q->whereHas('inventory.product', function ($p) use ($searchProductName) {
                $p->where('product_name', 'like', '%' . $searchProductName . '%');
            });
        });

        // Filter by category ID (name)
        $query->when($request->search_category_id, function ($q, $searchCategoryId) {
            $catIds = InventoryController::getIdsByCategoryName($searchCategoryId);
            if (!empty($catIds)) {
                $q->whereHas('inventory.product', function ($p) use ($catIds) {
                    $p->whereIn('category_id', $catIds);
                });
            }
        });

        // Update session branch_id
        if (session('branch_id') !== $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        // Filter by branch
        $query->when($request->branch_id, function ($q, $branchId) {
            $q->whereHas('inventory.branch', function ($b) use ($branchId) {
                $b->where('branch_id', $branchId);
            });
        });

        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$from, $to]);
        });

        return $query->groupBy('inventory_id', 'stock_id','grn_type')
                    ->orderByDesc('id')
                    ->orderByDesc('updated_at')
                    ;
    }
    private function getFormatPendingGrnStockReturnData($grns)
    {
        $updatedByMap = PendingGrnStockReturnUpdateBYrHelper::getUpdatedByMap($grns);
        return $grns
            ->unique(fn($item) => $item->inventory_id . '-' . $item->stock_id)
            ->filter(function ($item) {
                $totalGrnQty = round($item->totalGrnQty, 2);
                $orderQty = round($item->stock->qty, 2);
                $pendingGrnQty = round($orderQty - $totalGrnQty, 2);
                return $totalGrnQty < $orderQty && $totalGrnQty > 0 && $pendingGrnQty > 0;
            })
            ->map(function ($item) use ($updatedByMap) {
                $totalGrnQty = round($item->totalGrnQty, 2);
                $orderQty = round($item->stock->qty, 2);
                $pendingGrnQty = round($orderQty - $totalGrnQty, 2);

                $key = $item->inventory_id . '-' . $item->stock_id . '-' . $item->grn_type . '-' . $item->last_updated_at;
                $updatedById = $updatedByMap[$key] ?? null;
                $addedByName = $updatedById ? User::find($updatedById)->name : '';
                return [
                    'grn_no' =>$item->grn_no ?: 'N/A',
                    'stock_no' => $item->stock->stock_no,
                    'product_name' => $item->inventory->product->product_name ?? '',
                    'specification' => TruncateWithTooltipHelper::wrapText($item->inventory->specification),
                    'size' => TruncateWithTooltipHelper::wrapText($item->inventory->size),
                    'stock_vendor_name' => $item->stock->stock_vendor_name ?? '',
                    'added_by' => $addedByName,
                    'added_date' => Carbon::parse($item->last_updated_at)->format('d-m-Y'),
                    'uom' => $item->inventory->uom->uom_name ?? '',
                    'order_quantity' => NumberFormatterHelper::formatQty($orderQty, session('user_currency')['symbol'] ?? '₹'),
                    'total_grn_quantity' => NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹'),
                    'pending_grn_quantity' => NumberFormatterHelper::formatQty($pendingGrnQty, session('user_currency')['symbol'] ?? '₹'),
                ];
            })
            ->values()
            ->map(function ($item) {
                return $item;
            });
    }
    //end pingki

}






