<?php

namespace App\Http\Controllers\Buyer;

use App\Exports\{
    closeIndentReportExport,IndentReportExport
};
use App\Helpers\{
    NumberFormatterHelper,TruncateWithTooltipHelper
};
use App\Http\Controllers\Controller;
use App\Models\{
    Indent,Inventories,Grn,Rfq,RfqProductVariant
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Buyer\InventoryController;
use Carbon\Carbon;
use App\Services\ExportService;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class IndentController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    
    public function store(Request $request)
    {
        try {
            $request = $this->trimAndReturnRequest($request);

            $buyerId = Auth::user()->parent_id ?? Auth::user()->id;
            $userId = Auth::user()->id;

            if (!empty($request->indent_id)) {
                $data = [
                    'inventory_id'  => $request->inventory_id,
                    'indent_qty'    => $request->indent_qty,
                    'remarks'       => htmlspecialchars($request->remarks, ENT_QUOTES, 'UTF-8'),
                    'buyer_id'      => $buyerId,
                    'updated_by'    => $userId,
                    'inv_status'    => 1,
                    'is_active'     => 1,
                    'is_deleted'    => '2',
                    'closed_indent' => '2',
                ];

                $validator = Validator::make($data, [
                    'indent_qty' => ['required', 'numeric', 'min:0.01', 'max:9999999999', new NoSpecialCharacters(false)],
                    'remarks'    => ['nullable', 'string', 'max:250', new NoSpecialCharacters(true)],
                ]);

                if ($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
                }

                Indent::where('id', $request->indent_id)->update($data);

                return response()->json(['status' => true, 'message' => 'Indent updated successfully!'], 200);
            }

            $inventoryIds = $request->input('inventory_id');
            $indentQties  = $request->input('indent_qty');
            $remarksArr   = $request->input('remarks');

            if (empty($inventoryIds) || !is_array($inventoryIds)) {
                return response()->json(['status' => false, 'message' => 'No inventory selected.'], 422);
            }

            $lastIndent = Indent::where('buyer_id', $buyerId)->orderByDesc('inventory_unique_id')->first();
            $nextInventoryId = $lastIndent ? $lastIndent->inventory_unique_id + 1 : 1;

            $now = now();
            $insertData = [];
            $updateInventoryIds = [];

            foreach ($inventoryIds as $index => $inventoryId) {
                $qty = $indentQties[$index] ?? null;
                $remarks = $remarksArr[$index] ?? null;

                $validator = Validator::make([
                    'indent_qty' => $qty,
                    'remarks'    => $remarks,
                ], [
                    'indent_qty' => ['required', 'numeric', 'min:0.01', 'max:9999999999', new NoSpecialCharacters(false)],
                    'remarks'    => ['nullable', 'string', 'max:250', new NoSpecialCharacters(true)],
                ]);

                if ($validator->fails()) {
                    return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
                }

                $insertData[] = [
                    'inventory_id'        => $inventoryId,
                    'indent_qty'          => $qty,
                    'remarks'             => htmlspecialchars($remarks, ENT_QUOTES, 'UTF-8'),
                    'buyer_id'            => $buyerId,
                    'inventory_unique_id' => $nextInventoryId++,
                    'created_by'          => $userId,
                    'updated_by'          => $userId,
                    'inv_status'          => 1,
                    'is_active'           => 1,
                    'is_deleted'          => '2',
                    'closed_indent'       => '2',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                $updateInventoryIds[] = $inventoryId;
            }

            if (!empty($insertData)) {
                Indent::insert($insertData);
                Inventories::whereIn('id', $updateInventoryIds)->update(['is_indent' => 1]);
            }

            return response()->json(['status' => true, 'message' => 'Indents saved successfully!'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error saving Indent!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status' => 2,
                'message' => 'Invalid request type.'
            ]);
        }

        $request->validate([
            'indent_inventory_id' => 'required|integer'
        ]);

        $indent = Indent::with('inventory.branch')->find($id);

        if (!$indent) {
            return response()->json([
                'status' => 2,
                'message' => 'Indent not found.'
            ]);
        }
        $indent->is_deleted = 1;
        $indent->save();

        $inventoryId = $request->indent_inventory_id;
        $hasOtherIndents = Indent::where('inventory_id', $inventoryId)
            ->where('is_deleted', '2')
            ->exists();

        if (!$hasOtherIndents) {
            Inventories::where('id', $inventoryId)->update(['is_indent' => '2']);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Indent deleted successfully.',
        ]);
    }
    public function fetchIndentData(Request $request)
    {
        $inventoryId = $request->inventory;

        if (!$inventoryId) {
            return response()->json(['status' => 0, 'message' => 'Invalid Inventory ID'], 400);
        }

        $indentData = Indent::with(['createdBy', 'updatedBy'])
            ->where('inventory_id', $inventoryId)
            ->where('is_deleted', '2')
            ->get();

        if ($indentData->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'No Indent Found']);
        }

        $formattedData = $indentData->map(function ($indent) {
            return [
                'id' => $indent->id,
                'is_active' => $indent->is_active,
                'inventory_unique_id' => $indent->inventory_unique_id,
                'indent_qty' => NumberFormatterHelper::formatQty($indent->indent_qty,session('user_currency')['symbol'] ?? '₹'),
                'remarks' =>  htmlspecialchars_decode($indent->remarks, ENT_QUOTES),
                'created_at' => $indent->created_at,
                'created_by' => optional($indent->createdBy)->name,
                'updated_by' => optional($indent->updatedBy)->name,
            ];
        });

        return response()->json(['status' => 1, 'resp' => $formattedData]);
    }

    public function getIndentData(Request $request)
    {
        $indentId = $request->indent_id;

        if (!$indentId) {
            return response()->json(['status' => 0, 'message' => 'Invalid Indent ID'], 400);
        }

        $indent = Indent::with(['inventory.product'])->where('id', $indentId)->where('is_deleted', 2)->first();

        if (!$indent) {
            return response()->json(['status' => 0, 'message' => 'Indent not found']);
        }
        $inventoryId = $indent->inventory_id;
        $inventoryController = app(InventoryController::class);
        $inventoryController->preloadRfqData([$inventoryId]);
        $rfqData = $inventoryController->getRfqData($inventoryId);
        $rfqQty = $rfqData['rfq_qty'][$inventoryId] ?? 0;

        $restRfqQty = $rfqQty;
        $min_indent_qty=0;
        $allIndents = Indent::where('inventory_id', $inventoryId)
        ->where('is_deleted', 2)
        ->orderBy('id')
        ->get(['id', 'indent_qty']);

        $showDelete = true;
        if ($rfqQty <= 0) {
            $showDelete = true;
        } else {
            $showDelete = false;
            $restRfqQty = $rfqQty;
            foreach ($allIndents as $row) {
                if ($restRfqQty == 0) {
                    $showDelete = true;
                    $min_indent_qty=0;
                    break;
                }
                if ($restRfqQty >= $row->indent_qty) {
                    $restRfqQty -= $row->indent_qty;
                    if ($row->id == $indent->id) {
                        $min_indent_qty=$restRfqQty;
                        $showDelete = false;
                        break;
                    }
                } else {
                    $min_indent_qty = $restRfqQty;
                    $restRfqQty = 0;
                    if ($row->id == $indent->id) {
                        $showDelete = false;
                        break;
                    }
                   
                }
            }
        }

        

        $data = [
            'id' => $indent->id,
            'inventory_id' => $indent->inventory_id,
            'inventory_unique_id' => $indent->inventory_unique_id,
            'indent_qty' => round($indent->indent_qty,2),
            'remarks' => htmlspecialchars_decode($indent->remarks, ENT_QUOTES) ,
            'is_active' => $indent->is_active,
            'created_by' => $indent->created_by,
            'updated_by' => $indent->updated_by,
            'created_at' => $indent->created_at,
            'updated_at' => $indent->updated_at,
            'showDelete' => $showDelete,
            'min_indent_qty' => $min_indent_qty > 0 ? round($min_indent_qty,2) : 0,


            'product_name' => optional($indent->inventory->product)->product_name,
            'specification' => optional($indent->inventory)->specification,
            'size' => optional($indent->inventory)->size,
            'uom_id' => optional($indent->inventory)->uom_id,
            'uom_name' => optional($indent->inventory->uom)->uom_name,
        ];

        return response()->json(['status' => 1, 'data' => $data]);
    }
    //-----------------------------------------INDENT REPORT-------------------------------------------------------------------
    public function fetchIndentReportDataFilter(Request $request)
    {
        $query = Indent::with(['inventory.product', 'inventory.uom','inventory.branch', 'updatedBy']);
        if (session('branch_id') != $request->branch_id) {
                session(['branch_id' => $request->branch_id]);
            }
        $query->where('buyer_id',Auth::user()->parent_id ?? Auth::user()->id);

        $query->when($request->branch_id, function ($q) use ($request) {
            $q->whereHas('inventory.branch', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        })
        ->when($request->search_product_name, function ($q) use ($request) {
            $q->whereHas('inventory.product', function ($q) use ($request) {
                $q->where('product_name', 'like', "%{$request->search_product_name}%");
            });
        })
        ->when($request->search_category_id, function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', function ($q) use ($cat_id) {
                    $q->whereIn('category_id', $cat_id);
                });
            }
        })
        ->when($request->search_is_active, function ($q) use ($request) {
            $q->where('is_active', $request->search_is_active);
        })
        ->when($request->from_date && $request->to_date, function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $toDate = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$fromDate, $toDate]);
        });


        return $query->orderBy('created_at', 'desc')->orderBy('inventory_unique_id', 'desc');
    }
    public function getindentreportData(Request $request)
    {
        if (!$request->ajax()) return;
        $filteredQuery = $this->fetchIndentReportDataFilter($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $filteredQuery->paginate($perPage, ['*'], 'page', $page);

        $indents = collect($paginated->items());

        $data = $indents->map(function ($indent) {
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $indent_status = ['1' => 'Approved', '2' => 'Unapproved'];

            return [
                'IndentNumber' => $indent->inventory_unique_id,
                'product' => $indent->inventory->product->product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText($indent->inventory->specification),
                'size' => TruncateWithTooltipHelper::wrapText($indent->inventory->size),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($indent->inventory->inventory_grouping),
                'users' => TruncateWithTooltipHelper::wrapText(optional($indent->updatedBy)->name),
                'indent_qty' => NumberFormatterHelper::formatQty($indent->indent_qty, $currencySymbol) . ($indent->is_deleted == '1' ? ' (Deleted)' : ''),
                'uom' => $indent->inventory->uom->uom_name ?? '',
                'remarks' => TruncateWithTooltipHelper::wrapText(htmlspecialchars_decode($indent->remarks, ENT_QUOTES)),
                'status' => $indent_status[$indent->is_active] ?? 'Unapproved',
                'updated_at' => Carbon::parse($indent->updated_at)->format('d-m-Y'),
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
    public function exportIndentreportData(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_is_active',
                'from_date',
                'to_date',
            ]);

        $export = new IndentReportExport($filters, $currencySymbol);
        $fileName = 'Indent_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }

    //---------------------------------------------CLOSE INDENT REPORT--------------------------------------------------------------
    public function closeIndentReportDataFilter(Request $request)
    {
        $query = Indent::with([
            'inventory.product',
            'inventory.branch',
            'inventory.uom',
            'updatedBy'
        ])
        ->where('closed_indent', 1)
        ->where('buyer_id', Auth::user()->parent_id ?? Auth::user()->id);

        $query->when($request->search_category_id, function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', function ($q) use ($cat_id) {
                    $q->whereIn('category_id', $cat_id);
                });
            }
        });

        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query->when($request->branch_id, function ($q) use ($request) {
            $q->whereHas('inventory.branch', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        });

        $query->when($request->search_product_name, function ($q) use ($request) {
            $q->whereHas('inventory.product', function ($q) use ($request) {
                $q->where('product_name', 'like', "%{$request->search_product_name}%");
            });
        });

        $query->when($request->from_date && $request->to_date, function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $toDate = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();
            $q->whereBetween('updated_at', [$fromDate, $toDate]);
        });

        $indents = $query->orderBy('updated_at', 'desc')->get();

        $grouped = $indents->groupBy('inventory_id')->map(function ($group) {
            $first = $group->first();
            $first->indent_qty = $group->sum('indent_qty');
            return $first;
        });

        return $grouped->values();
    }


    public function getcloseindentreportData(Request $request)
    {
        if (!$request->ajax()) return;

        $filteredCollection = $this->closeIndentReportDataFilter($request);

        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;

        $paginated = new LengthAwarePaginator(
            $filteredCollection->forPage($page, $perPage),
            $filteredCollection->count(),
            $perPage,
            $page
        );

        $closeIndents = collect($paginated->items());

        $data = $closeIndents->map(function ($row) {
            $inventory = $row->inventory;
            $inventoryId = $row->inventory_id;


            $inventoryController = app(InventoryController::class);
            // $this->controller->preloadGrnData([$inventoryId]);
            // $this->controller->preloadRfqData([$inventoryId]);
            // $this->controller->preloadOrderData([$inventoryId]);
            $totalGrnQty = $this->getGrnData($inventoryId)['grn_qty'][$inventoryId] ?? 0;
            $totalRfqQty = $this->getRfqData($inventoryId)['rfq_qty'][$inventoryId] ?? 0;
            $totalOrderQty = $this->getOrderData($inventoryId)['order_qty'][$inventoryId] ?? 0;

            return [
                'details' => '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' close_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-dash-lg"></i></span>
                    <span data-toggle="collapse" style="cursor: pointer" id="plus_' . $inventoryId . '" class="pr-2 accordion_parent accordion_parent_' . $inventoryId . ' open_indent_tds" tab-index="' . $inventoryId . '"><i class="bi bi-plus-lg"></i></span>',
                'product' => $inventory->product->product_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText($inventory->specification),
                'size' => TruncateWithTooltipHelper::wrapText($inventory->size),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inventory->inventory_grouping),
                'users' => $row->updatedBy->name ?? '',
                'uom' => $inventory->uom->uom_name ?? '',
                'indent_qty' => NumberFormatterHelper::formatQty($row->indent_qty, session('user_currency')['symbol'] ?? '₹'),
                'rfq_qty' => $totalRfqQty > 0 ? NumberFormatterHelper::formatQty($totalRfqQty, session('user_currency')['symbol'] ?? '₹') : 0,
                'order_qty' => $totalOrderQty > 0 ? NumberFormatterHelper::formatQty($totalOrderQty, session('user_currency')['symbol'] ?? '₹') : 0,
                'grn_qty' => $totalGrnQty > 0 ? NumberFormatterHelper::formatQty($totalGrnQty, session('user_currency')['symbol'] ?? '₹') : 0,
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

    public function getGrnData($inventoryId)
    {
        $grnQtySum = Grn::where('inventory_id', $inventoryId)
            ->where('grn_type', 1)
            ->where('inv_status', 2)
            ->sum('grn_qty');

        return [
            'grn_qty' => [
                $inventoryId => $grnQtySum
            ]
        ];
    }
    public function getRfqData($inventoryIds): array
    {
        $result = [
            'already_fetch_rfq' => [],
            'close_rfq_id_arr' => [],
            'rfq_ids_against_inventory_id' => [],
            'rfq_qty' => [],
        ];
        $rfqs = Rfq::with('rfqProductVariants')
            ->where('record_type', 2)
            ->whereHas('rfqProductVariants', function ($query) use ($inventoryIds) {
                if (is_array($inventoryIds)) {
                    $query->whereIn('inventory_id', $inventoryIds)
                          ->where('inventory_status', 2);
                } else {
                    $query->where('inventory_id', $inventoryIds)
                          ->where('inventory_status', 2);
                }
            })
            ->get();
        foreach ($rfqs as $rfq) {
            $rfqId = $rfq->id;
            $status = $rfq->buyer_rfq_status;

            $result['already_fetch_rfq'][$rfqId] = $rfqId;

            foreach ($rfq->rfqProductVariants as $variant) {
                $inventoryId = $variant->inventory_id;
                $quantity = $variant->quantity;

                if (in_array($status, [8, 10])) {
                    $result['close_rfq_id_arr'][$rfqId] = $rfqId;
                    $result['rfq_ids_against_inventory_id'][$rfqId] = $inventoryId;
                } else {
                    if (!isset($result['rfq_qty'][$inventoryId])) {
                        $result['rfq_qty'][$inventoryId] = 0;
                    }
                    $result['rfq_qty'][$inventoryId] += $quantity;
                }
            }
        }
        return $result;
    }

    public function getOrderData($inventoryId): array
    {
        $totalQty = 0;

        $variants = RfqProductVariant::with(['rfq.orders.order_variants'])
            ->where('inventory_id', $inventoryId)
            ->where('inventory_status', 2)
            ->whereHas('rfq', function ($query) {
                $query->where('record_type', 2);
            })
            ->get();

        foreach ($variants as $variant) {
            $rfq = $variant->rfq;

            foreach ($rfq->orders as $order) {
                if ($order->order_status != 1) continue;

                foreach ($order->order_variants as $ov) {
                    if ($ov->product_id == $variant->product_id) {
                        $totalQty += $ov->order_quantity;
                    }
                }
            }
        }

        return ['order_qty' => [$inventoryId => $totalQty]];
    }
    public function exportCloseIndentData(Request $request)
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

            $export = new closeIndentReportExport($filters, $currencySymbol);
            $fileName = 'Close_Indent_Report_' . now()->format('d-m-Y') . '.xlsx';

            $response = $this->exportService->storeAndDownload($export, $fileName);

            return response()->json($response);
        }
    }
    public function closeindentdata(Request $request)
    {
        $inventoryId = $request->inventory;

        if (!$inventoryId) {
            return response()->json(['status' => 0, 'message' => 'Invalid Inventory ID'], 400);
        }

        $indentData = Indent::with(['createdBy', 'updatedBy'])
            ->select(
                'id','is_active','inventory_unique_id','indent_qty','remarks','created_at','created_by', 'updated_by'
            )
            ->where('inventory_id', $inventoryId)
            ->where('closed_indent', 1)
            ->get();

        if ($indentData->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'No Indent Found']);
        }

        $formattedData = $indentData->map(function ($indent) {
            return [
                'id' => $indent->id,
                'is_active' => $indent->is_active,
                'inventory_unique_id' => $indent->inventory_unique_id,
                'indent_qty' => $indent->indent_qty,
                'remarks' => $indent->remarks,
                'created_at' => $indent->created_at,
                'created_by' => $indent->createdBy->name ?? null,
                'updated_by' => $indent->updatedBy->name ?? null
            ];
        });

        return response()->json(['status' => 1, 'resp' => $formattedData]);
    }
}
