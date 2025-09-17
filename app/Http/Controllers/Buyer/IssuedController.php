<?php

namespace App\Http\Controllers\Buyer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Validator};
use Carbon\Carbon;

use App\Models\{Issued, Grn, IssuedReturn, Issueto, Inventories,ReturnStock};
use App\Helpers\{NumberFormatterHelper, TruncateWithTooltipHelper,StockQuantityHelper};
use App\Http\Controllers\Buyer\InventoryController;

use App\Exports\IssueReportExport;
use App\Services\ExportService;
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;

use App\Http\Controllers\Controller;
class IssuedController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    //start issue to
    public function getissuedto(Request $request){
        $issuedtoData = Issueto::all();
        if ($issuedtoData->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'No Indent Found']);
        }
        return response()->json(['status' => 1, 'data' => $issuedtoData]);
    }

    public function saveIssueTo(Request $request)
    {
        try {
            $request = $this->trimAndReturnRequest($request);
            $nameArr      = $request->input('issue_to_name');
            $issueToIdArr = $request->input('issue_to_id');

            if (empty($nameArr)) {
                return response()->json(['status' => 0, 'msg' => 'No data received.']);
            }

            $insertData = [];
            $updateData = [];

            foreach ($nameArr as $key => $name) {
                if (empty(trim($name))) {
                    continue; // Skip empty names
                }

                $issueToId = $issueToIdArr[$key] ?? null;

                if (!empty($issueToId) && $issueToId >= 1) {
                    $updateData[] = [
                        'id'           => $issueToId,
                        'name'         => trim($name),
                        'updated_by'   => Auth::user()->id,
                        'updated_at' => now(),
                    ];
                } else {
                    $insertData[] = [
                        'user_id'      => Auth::user()->id,
                        'name'         => trim($name),
                        'created_by'     => Auth::user()->id,
                        'updated_by'   => Auth::user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            $success = false;

            if (!empty($insertData)) {
                Issueto::insert($insertData);
                $success = true;
            }

            if (!empty($updateData)) {
                foreach ($updateData as $data) {
                    Issueto::where('id', $data['id'])->update([
                        'name'        => $data['name'],
                        'updated_by'  => $data['updated_by'],
                        'updated_at'  => $data['updated_at'],
                    ]);
                }
                $success = true;
            }

            if ($success) {
                return response()->json(['status' => 1, 'msg' => 'Issue To data saved successfully.']);
            }

            return response()->json(['status' => 0, 'msg' => 'Nothing to save.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => 'Failed to save Issue To.', 'error' => $e->getMessage()]);
        }
    }

    public function deleteIssueTo(Request $request)
    {
        $dbid = $request->input('dbid');

        if (!$dbid) {
            return response()->json(['status' => 0, 'msg' => 'Invalid ID']);
        }

        try {
            $deleted = Issueto::where('id', $dbid)->delete();
            if ($deleted) {
                return response()->json(['status' => 1, 'msg' => 'Issue To deleted successfully.']);
            } else {
                return response()->json(['status' => 0, 'msg' => 'Record not found or already deleted.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'msg' => 'Something went wrong.']);
        }
    }
//end issue to

//start add issue
    public function fetchInventoryDetails(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|integer|exists:inventories,id',
        ]);

        $inventory = Inventories::with(['product', 'uom'])->find($request->inventory_id);

        if (!$inventory) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory not found.'
            ], 404);
        }
        $inventoryIds = $inventory->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($request->inventory_id,$inventory->opening_stock,$quantityMaps);
        if ($currentStockValue == 0) {
            return response()->json([
                'status' => 0,
                'message' => 'No stock available to issue.'
            ]);
        }


        return response()->json([
            'status' => 1,
            'data' => [
                'product_name'    => $inventory->product->product_name ?? '',
                'specification'   => $inventory->specification ?? '',
                'size'            => $inventory->size ?? '',
                'uom_name'        => $inventory->uom->uom_name ?? '',
                'uom_id'          => $inventory->uom->id ?? '',
                'issuedtoList'    => Issueto::all(['id', 'name']),
                'issuefromList' => $this->getIssueFromList($inventory),
            ]
        ]);
    }
    public static function getIssueFromList($inventory)
    {
        $issueFromList = [];

        // Calculate Opening Stock Remaining
        $issuedQty = Issued::where('inventory_id', $inventory->id)
            ->where('issued_return_for', 0)
            ->where('is_deleted', '2')
            ->sum('qty');

        $issuedReturnQty = IssuedReturn::where('inventory_id', $inventory->id)
            ->where('issued_return_for', 0)
            ->where('is_deleted', '2')
            ->sum('qty');

        $stockReturnQty = ReturnStock::where('inventory_id', $inventory->id)
            ->where('stock_return_for', 0)
            ->where('is_deleted', '2')
            ->sum('qty');

        $stockReturnGrnQty = Grn::where('inventory_id', $inventory->id)
            ->where('stock_return_for', 0)            
            ->where('order_id', 0)
            ->where('grn_type', '3')
            ->where('is_deleted', '2')
            ->sum('grn_qty');

        $availableOpeningStock = $inventory->opening_stock - $issuedQty + $issuedReturnQty - $stockReturnQty + $stockReturnGrnQty;

        if ($availableOpeningStock > 0) {
            $issueFromList[] = [
                'label' => 'Opening Stock',
                'id' => 0,
                'stock' => NumberFormatterHelper::formatQty($availableOpeningStock,session('user_currency')['symbol'] ?? '₹'),
                'stockQty' => $availableOpeningStock,
                'order_id' => null,
                'type' => 'opening'
            ];
        }

        $grns = self::getGrnsByInventory($inventory->id)->filter();


        foreach ($grns as $grn) {
            $issueFromList[] = [
                'label' => $grn['label'],
                'id' => $grn['id'],
                'stock' => NumberFormatterHelper::formatQty($grn['stock'],session('user_currency')['symbol'] ?? '₹'),
                'stockQty' => $grn['stock'],
                'order_id' => $grn['order_id'],
                'type' => $grn['type']
            ];
        }

        return $issueFromList;
    }

    public static function getGrnsByInventoryOld($inventoryId)
    {
        return Grn::where('inventory_id', $inventoryId)
            ->selectRaw('inventory_id, order_id, grn_type, SUM(grn_qty) as grn_qty')
            ->groupBy('inventory_id', 'order_id', 'grn_type')
            ->get()
            ->map(function ($group) {
                $first = Grn::with(['manualOrderProduct', 'manualOrder'])
                    ->where([
                        'inventory_id' => $group->inventory_id,
                        'order_id' => $group->order_id,
                        'grn_type' => $group->grn_type,
                    ])
                    ->orderBy('id')
                    ->first();

                $issuedQty = Issued::where('inventory_id', $group->inventory_id)
                    ->where('issued_return_for', $first->id ?? 0)
                    ->where('is_deleted', '2')
                    ->sum('qty');

                $issuedReturnQty = IssuedReturn::where('inventory_id', $group->inventory_id)
                    ->where('issued_return_for', $first->id ?? 0)
                    ->where('is_deleted', '2')
                    ->sum('qty');

                $stockReturnQty = ReturnStock::where('inventory_id', $group->inventory_id)
                    ->where('stock_return_for', $first->id ?? 0)
                    ->where('is_deleted', '2')
                    ->sum('qty');
                $stock=$group->grn_qty - $issuedQty+$issuedReturnQty-$stockReturnQty;
                if($stock>0 && $group->order_id!='0'){
                    return [
                        'label' => $group->po_number.'/'.$group->vendor_name,
                        'id' => $first->id ?? null,
                        'stock' => $stock,
                        'order_id' => $group->order_id,
                        'grn_type' => $group->grn_type,
                        'order_qty' => $first->order_qty ?? null,
                        'type' => 'grn',
                    ];
                }

            });
    }
    public static function getGrnsByInventory($inventoryId)
    {
        $baseQuery = Grn::where('inventory_id', $inventoryId)
            ->whereIn('grn_type', [1, 2, 4]);

        $results = $baseQuery->get()
            ->groupBy(function ($item) {
                if (in_array($item->grn_type, [1, 4])) {
                    return $item->inventory_id . "-" . $item->order_id . "-" . $item->grn_type;
                } elseif ($item->grn_type == 2) {
                    return $item->inventory_id . "-" . $item->order_no . "-" . $item->grn_type;
                }
            });

        return $results->map(function ($groups) {
            $grnQty = $groups->sum('grn_qty');
            $group = $groups->first();

            $first = Grn::with(['manualOrderProduct', 'manualOrder'])
                ->where('inventory_id', $group->inventory_id)
                ->where('grn_type', $group->grn_type)
                ->when(in_array($group->grn_type, [1, 4]), function ($q) use ($group) {
                    $q->where('order_id', $group->order_id);
                })
                ->when($group->grn_type == 2, function ($q) use ($group) {
                    $q->where('order_no', $group->order_no);
                })
                ->orderBy('id')
                ->first();

            $issuedQty = Issued::where('inventory_id', $group->inventory_id)
                ->where('issued_return_for', $first->id ?? 0)
                ->where('is_deleted', '2')
                ->sum('qty');

            $issuedReturnQty = IssuedReturn::where('inventory_id', $group->inventory_id)
                ->where('issued_return_for', $first->id ?? 0)
                ->where('is_deleted', '2')
                ->sum('qty');

            $stockReturnQty = ReturnStock::where('inventory_id', $group->inventory_id)
                ->where('stock_return_for', $first->id ?? 0)
                ->where('is_deleted', '2')
                ->sum('qty');

            $stockReturnGrnQty = Grn::where('inventory_id', $group->inventory_id)
                ->where('stock_return_for', $first->id ?? 0) 
                ->where('grn_type', '3')
                ->where('is_deleted', '2')
                ->sum('grn_qty');

            $stock = $grnQty - $issuedQty + $issuedReturnQty - $stockReturnQty + $stockReturnGrnQty;

            if ($stock > 0) {
                return [
                    'label' => !empty($first->po_number) && !empty($first->vendor_name)
                        ? $first->po_number . '/' . $first->vendor_name
                        : $first->order_no . '/' . $first->vendor_name,
                    'id' => $first->id ?? null,
                    'stock' => $stock,
                    'order_id' => $group->order_id ?? null,
                    'grn_type' => $group->grn_type,
                    'order_qty' => $first->order_qty ?? null,
                    'type' => 'grn',
                ];
            }
        })->filter()->values();
    }


    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);

        $attributeNames = [
            'qty' => 'quantity',
            'inventory_id' => 'inventory',
            'issued_return_for' => 'issue From',
            'issued_to' => 'issue to',
        ];

        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventories,id',
            'qty' => 'required|numeric|min:0.01',
            'issued_to' => 'nullable|exists:issue_to,id',
            'remarks' => ['nullable', 'string', 'max:255', new NoSpecialCharacters(true)],
            'issued_return_for' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value != 0 && !Grn::where('id', $value)->exists()) {
                        $fail('The selected issue from source is invalid.');
                    }
                },
            ],
        ]);

        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $inventory = Inventories::find($request->inventory_id);
        $issueSources = self::getIssueFromList($inventory);

        $matchedSource = collect($issueSources)->first(function ($source) use ($request) {
            return (int) $source['id'] === (int) $request->issued_return_for;
        });

        if (!$matchedSource) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid or unavailable issue source.',
            ], 400);
        }

        if ($request->qty > $matchedSource['stockQty']) {
            return response()->json([
                'status' => 0,
                'message' => 'Requested quantity exceeds available stock from the selected source.',
            ], 400);
        }

        $issue = new Issued();
        $issue->inventory_id = $request->inventory_id;
        $issue->buyer_id = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

        $lastIssue = Issued::where('buyer_id', $issue->buyer_id)
            ->orderBy('issued_no', 'desc')
            ->first();

        $nextIssueNo = $lastIssue ? $lastIssue->issued_no + 1 : 1;

        $issue->issued_no = $nextIssueNo;
        $issue->qty = $request->qty;
        $issue->issued_return_for = $request->issued_return_for;
        $issue->issued_to = $request->issued_to;
        $issue->updated_by = Auth::user()->id;
        $issue->updated_at = now();
        $issue->remarks = htmlspecialchars($request->remarks ?? '', ENT_QUOTES, 'UTF-8');
        $issue->save();

        return response()->json([
            'status' => 1,
            'message' => 'Issued quantity updated successfully',
            'data' => $issue
        ], 201);
    }


//end add issue

//start issue report
    public function applyFilters(Request $request)
    {
        $query = Issued::with([
            'inventory:id,product_id,uom_id,specification,size,inventory_grouping,stock_price,buyer_branch_id',
            'inventory.product:id,product_name,category_id,division_id',
            'inventory.product.category:id,category_name',
            'inventory.product.division:id,division_name',
            'inventory.uom:id,uom_name',
            'updater:id,name',
            'inventory.branch',
            'issuedTo:id,name',
            'grn.manualOrderProduct',
            'grn.Order',
            'grn.Order.order_variants'
        ])->orderBy('id', 'desc');

        if (session('branch_id') != $request->branch_id) {
            session(['branch_id' => $request->branch_id]);
        }

        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->whereHas('inventory.branch', function ($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->whereHas('inventory.product', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->search_product_name . '%');
            });
        });

        $query->when($request->filled('search_issueto_id'), function ($q) use ($request) {
            $q->where('issued_to', $request->search_issueto_id);
        });

        $query->when($request->filled('search_buyer_id'), function ($q) use ($request) {
            $q->where('updated_by', $request->search_buyer_id);
        });

        $query->when(!empty($request->search_category_id), function ($q) use ($request) {
            $cat_id = InventoryController::getIdsByCategoryName($request->search_category_id);
            if (!empty($cat_id)) {
                $q->whereHas('inventory.product', function ($q) use ($cat_id) {
                    $q->whereIn('category_id', $cat_id);
                });
            }
        });

        $query->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
            $fromDate = Carbon::createFromFormat('d-m-Y', trim($request->input('from_date')))->startOfDay();
            $toDate = Carbon::createFromFormat('d-m-Y', trim($request->input('to_date')))->endOfDay();
            $q->whereBetween('updated_at', [$fromDate, $toDate]);
        });

        $query->where('buyer_id',Auth::user()->parent_id ?? Auth::user()->id);
        return $query;
    }
    public function getIssuedListData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->applyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();
        $data = $this->formatIssuedData($inventories);

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
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'search_issueto_id',
                'search_buyer_id',
                'from_date',
                'to_date',
            ]);

        $export = new IssueReportExport($filters, $currencySymbol);
        $fileName = 'Issue_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    private function formatIssuedData($rows)
    {
        $data = [];
        foreach ($rows as $row) {
            $inventoryId = $row->inventory->id;
            $issuedReturnFor = $row->issued_return_for;

            $totalIssuedReturnQty = IssuedReturn::where('inventory_id', $inventoryId)
                ->where('issued_return_for', $issuedReturnFor)
                ->sum('qty');

            $maxAllowQty = 0;
            $issuedRows = Issued::where('inventory_id', $inventoryId)
                ->where('issued_return_for', $issuedReturnFor)
                ->orderBy('id')
                ->get();

            foreach ($issuedRows as $issuedRow) {
                $availableQty = $issuedRow->qty - $issuedRow->consume_qty;

                if ($availableQty >= $totalIssuedReturnQty) {
                    $maxAllowQty = round($availableQty - $totalIssuedReturnQty, 2);
                    $totalIssuedReturnQty = 0;
                } else {
                    $totalIssuedReturnQty -= $availableQty;
                    $maxAllowQty = 0;
                }

                if ($issuedRow->id === $row->id) {
                    break;
                }
            }

            $issue_return_qty = NumberFormatterHelper::formatQty($row->qty - $maxAllowQty - $row->consume_qty, session('user_currency')['symbol'] ?? '₹');
            $data[] = [
                'Select' => round($maxAllowQty, 2) == 0
                    ? '<a href="#" class="text-primary open-consume" data-id="' . $row->id . '" data-maxqty="' . NumberFormatterHelper::formatQty($maxAllowQty, session('user_currency')['symbol'] ?? '₹') . '" data-stockqty="' . round($maxAllowQty, 2) . '" data-issuereturnqty="' . $issue_return_qty . '" data-consumeqty="' . NumberFormatterHelper::formatQty($row->consume_qty, session('user_currency')['symbol'] ?? '₹') . '">
                        <i class="bi bi-c-circle"></i>
                    </a>'
                    : '<input type="checkbox" name="id[]" value="' . $row->id . '" data-maxqty="' . NumberFormatterHelper::formatQty($maxAllowQty, session('user_currency')['symbol'] ?? '₹') . '" data-stockqty="' . round($maxAllowQty, 2) . '" data-issuereturnqty="' . $issue_return_qty . '" data-consumeqty="' . NumberFormatterHelper::formatQty($row->consume_qty, session('user_currency')['symbol'] ?? '₹') . '">',
                'issued_number' => $row->issued_no,
                'product' => $row->inventory->product->product_name ?? '',
                'division' => $row->inventory->product->division->division_name ?? '',
                'category' => $row->inventory->product->category->category_name ?? '',
                'specification' => TruncateWithTooltipHelper::wrapText($row->inventory->specification ?? ''),
                'size' =>TruncateWithTooltipHelper::wrapText($row->inventory->size ?? ''),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($row->inventory->inventory_grouping ?? ''),
                'issued_quantity' => NumberFormatterHelper::formatQty($row->qty, session('user_currency')['symbol'] ?? '₹'),
                'uom' => $row->inventory->uom->uom_name ?? '',
                'amount' => NumberFormatterHelper::formatCurrency($row->amount, session('user_currency')['symbol'] ?? '₹'),
                'added_bY' => $row->updater->name ?? '',
                'added_date' => $row->updated_at ? Carbon::parse($row->updated_at)->format('d-m-Y') : '',
                'remarks' =>TruncateWithTooltipHelper::wrapText($row->remarks ?? ''),
                'issued_to' => $row->issuedTo->name ?? '',
            ];
        }
        return $data;
    }
    public function ConsumeStore(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $request->validate([
            'issue_id' => ['required', 'exists:issued,id', new NoSpecialCharacters(false)],
            'qty'      => ['required', 'numeric', 'min:0.01', new NoSpecialCharacters(false)],
        ],
        [
            'qty.min' => 'Minimum quantity must be 0.01.',
        ]);

        $issue = Issued::findOrFail($request->issue_id);
        $maxQty = $issue->qty - $issue->consume_qty;

        if ($request->qty > $maxQty) {
            return response()->json([
                'status' => false,
                'message' => 'Quantity exceeds the available balance for this issue.'
            ], 422);
        }

        $totals = Issued::where('issued_return_for', $issue->issued_return_for)->where('inventory_id', $issue->inventory_id)
            ->selectRaw('SUM(qty) as total_qty, SUM(consume_qty) as total_consume_qty')
            ->first();

        $totalIssueQty = $totals->total_qty ?? 0;
        $totalConsumeQty = $totals->total_consume_qty ?? 0;
        $totalIssueReturnQty = IssuedReturn::where('issued_return_for', $issue->issued_return_for)->where('inventory_id', $issue->inventory_id)->sum('qty') ?? 0;
        $availableQty = round($totalIssueQty - $totalConsumeQty - $totalIssueReturnQty,2);
        $request->qty;
        if ($request->qty > $availableQty) {
            return response()->json([
                'status' => false,
                'message' => 'Quantity exceeds available stock. Please refresh the page.'
            ], 422);
        }

        $issue->consume_qty += $request->qty;
        $issue->save();

        return response()->json([
            'status' => true,
            'message' => 'Consume quantity successfully updated.'
        ]);
    }
//end issue report

}
