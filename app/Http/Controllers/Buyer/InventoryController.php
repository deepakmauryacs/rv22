<?php
namespace App\Http\Controllers\Buyer;
use App\Exports\{
    CurrentStockExport,InventoryExport,StockLedgerExport
};
use App\Helpers\{
    CurrentStockReportAmountHelper,NumberFormatterHelper,StockQuantityHelper,TruncateWithTooltipHelper
};
use App\Http\Controllers\Controller;
use App\Models\{
    BranchDetail,Category,Inventories,InventoryType,Uom,Grn, Indent, Issued, ManualOrder,ReturnStock,Rfq,RfqProduct,RfqProductVariant
};
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Auth,DB,Route,Validator
};
use App\Rules\NoSpecialCharacters;
use App\Traits\TrimFields;

class InventoryController extends Controller
{
    use TrimFields;
    public function __construct(protected ExportService $exportService) {}
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');

        $productId = $request->input('product_id');

        $categoryId = $request->input('category_id');
        $inventoryTypeId = $request->input('inventory_type_id');
        $user_id=(Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;
        $branches = BranchDetail::getDistinctActiveBranchesByUser($user_id);

        $categories = $this->getSortedUniqueCategoryNames();
        $inventoryTypes = InventoryType::all();
        $uom = Uom::all();
        $firstBranch = BranchDetail::getDistinctActiveBranchesByUser($user_id)->first();
        if(empty(session('branch_id'))){
            session(['branch_id' => $firstBranch->branch_id]);
        }
        session(['page_title' => 'Inventory Management System - Raprocure']);
        return view('buyer.inventory.index', compact('branches', 'categories', 'inventoryTypes', 'uom'));
    }

    public function store(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $attributeNames = [
            'buyer_product_name' => 'Our Product Name',
            'buyer_branch_id'    => 'Branch',
        ];

        $validator = Validator::make($request->all(), [
            'inventory_unique_id' => 'nullable|integer',
            'buyer_parent_id'     => 'nullable|integer|min:0',
            'buyer_branch_id'     => 'required|exists:branch_details,branch_id',
            'product_id'          => 'required|exists:products,id',

            'product_name'        => ['nullable', 'string', 'max:100', new NoSpecialCharacters(false)],
            'buyer_product_name'  => ['nullable', 'string', 'max:100', new NoSpecialCharacters(false)],
            'specification'       => ['nullable', 'string', 'max:3000', new NoSpecialCharacters(true)],
            'size'                => ['nullable', 'string', 'max:1500', new NoSpecialCharacters(true)],
            'opening_stock'       => ['required', 'regex:/^\d+(\.\d+)?$/', 'max:20', new NoSpecialCharacters(false)],
            'inventory_grouping'  => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
            'product_brand'       => ['nullable', 'string', 'max:255', new NoSpecialCharacters(false)],
            'stock_price'         => ['required', 'numeric', 'min:0', new NoSpecialCharacters(false)],
            'indent_min_qty'      => ['nullable', 'regex:/^\d+(\.\d+)?$/', 'max:20', new NoSpecialCharacters(false)],

            'uom_id'              => 'required|exists:uom,id',
            'inventory_type_id'   => 'nullable|integer|min:1',
        ]);

        $validator->setAttributeNames($attributeNames);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $requestData = $request->except(['_token']);

            if ($request->id >= 1) {
                $count = Inventories::where('product_id', $request->product_id)
                    ->where('specification', $request->specification)
                    ->where('size', $request->size)
                    ->where('id', '<>', $request->id)
                    ->where('buyer_branch_id', $request->buyer_branch_id)
                    ->count();
            } else {
                $count = Inventories::where('product_id', $request->product_id)
                    ->where('specification', $request->specification)
                    ->where('size', $request->size)
                    ->where('buyer_branch_id', $request->buyer_branch_id)
                    ->count();
            }

            if ($count == 0) {
                $requestData['buyer_parent_id'] = (Auth::user()->parent_id != 0) ? Auth::user()->parent_id : Auth::user()->id;

                if (empty($request->id)) {
                    $lastInventory = Inventories::where('buyer_parent_id', $requestData['buyer_parent_id'])
                        ->where('buyer_branch_id', $request->buyer_branch_id)
                        ->orderBy('inventory_unique_id', 'desc')
                        ->first();

                    $nextInventoryId = $lastInventory ? $lastInventory->inventory_unique_id + 1 : 1;
                    $requestData['inventory_unique_id'] = $nextInventoryId;
                    $requestData['created_by'] = Auth::user()->id;
                }

                $requestData['updated_by'] = Auth::user()->id;
                $requestData['indent_min_qty'] = round($request->indent_min_qty,2);
                $requestData['specification'] = htmlspecialchars($request->specification, ENT_QUOTES, 'UTF-8');
                $requestData['size'] = htmlspecialchars($request->size, ENT_QUOTES, 'UTF-8');

                $inventory = Inventories::updateOrCreate(
                    ['id' => $request->id],
                    $requestData
                );

                return response()->json([
                    'status' => true,
                    'message' => 'Inventory saved successfully!',
                    'data' => $inventory
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please Add Unique Size Or Specification With this Product!',
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error saving inventory!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        $data = Inventories::with([
            'product:id,product_name,category_id,division_id',
            'product.category:id,category_name',
            'product.division:id,division_name'
        ])
        ->select(
            'id', 'product_id', 'buyer_product_name', 'specification', 'size',
            'opening_stock', 'stock_price', 'uom_id', 'inventory_grouping',
            'inventory_type_id', 'indent_min_qty', 'product_brand'
        )
        ->find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory not found!'
            ], 404);
        }
        // start code pingki
        $editData = 1;
        $nonEditEnv = 0;

        if (Indent::where('inventory_id', $id)->exists()) {
            $editData = 0;
        }

        if (Issued::where('inventory_id', $id)->exists()) {
            $editData = 0;
            $nonEditEnv = 1;
        }

        if (Grn::where('inventory_id', $id)->exists()) {
            $editData = 0;
        }

        if (ReturnStock::where('inventory_id', $id)->exists()) {
            $editData = 0;
            $nonEditEnv = 1;
        }
        return response()->json([
            'success' => true,
            'data' => $data,
            'edit_data' => $editData,
            'non_edit_env' => $nonEditEnv,
            'specification'=>htmlspecialchars_decode($data->specification, ENT_QUOTES)
        ], 200);
        //end code pingki
    }

    public static function getSortedUniqueCategoryNames()
    {
        return Category::where('status', 1)
            ->select('category_name')
            ->distinct()
            ->orderBy('category_name', 'asc')
            ->pluck('category_name');
    }

    //-------------------------------------INVENTORY LIST & STOCK LEDGER REPORT ----------------------------------------------------
    public function getData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->applyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();

        $data1 = Route::currentRouteName() === 'buyer.report.stockLedger.listdata'
            ? $this->fetchStockledgerData($inventories)
            : $this->formatInventoryData($inventories);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }

    public function exportInventoryData(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $routeName = Route::currentRouteName();
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
            ]);
        if ($routeName === 'buyer.report.stockLedger.export') {
            $export = new StockLedgerExport($filters, $currencySymbol);
            $fileName = 'Stock_Ledger_Report_' . now()->format('d-m-Y') . '.xlsx';
        } else {
            $export = new InventoryExport($filters, $currencySymbol);
            $filters['ind_non_ind'] = $request->input('ind_non_ind');
            $filters['search_inventory_type_id'] = $request->input('search_inventory_type_id');
            $fileName = 'Inventory_Report_' . now()->format('d-m-Y') . '.xlsx';
        }

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    public function applyFilters($request): Builder
    {
        $query = Inventories::select([
                    'inventories.id','inventories.buyer_branch_id','inventories.product_id','inventories.buyer_product_name','inventories.specification','inventories.size','inventories.product_brand','inventories.inventory_grouping','inventories.inventory_type_id','inventories.uom_id','inventories.opening_stock','inventories.indent_min_qty',
                ])
                ->join('products', 'products.id', '=', 'inventories.product_id')
                ->with([
                    'product:id,product_name,category_id',
                    'product.category:id,category_name',
                    'branch:branch_id,name',
                    'uom:id,uom_name',
                ]);

        $user = Auth::user();
        $query->where('buyer_parent_id', $user->parent_id ?? $user->id);

        $query->when($request->filled('branch_id') && session('branch_id') != $request->branch_id, function () use ($request) {
            session(['branch_id' => $request->branch_id]);
        });

        $query->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('buyer_branch_id', $request->branch_id);
        });

        $query->when($request->filled('search_product_name'), function ($q) use ($request) {
            $q->whereHas('product', function ($q2) use ($request) {
                $q2->where('product_name', 'like', '%' . $request->search_product_name . '%');
            });
        });
        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
            if (!empty($categoryIds)) {
                $q->whereHas('product.category', function ($q2) use ($categoryIds) {
                    $q2->whereIn('id', $categoryIds);
                });
            }
        });


        $query->when($request->filled('ind_non_ind'), function ($q) use ($request) {
            $indentMap = [2 => 1, 3 => 2];
            if (isset($indentMap[$request->ind_non_ind])) {
                $q->where('is_indent', $indentMap[$request->ind_non_ind]);
            }
        });


        $query->when($request->filled('search_inventory_type_id'), function ($q) use ($request) {
            $q->where('inventory_type_id', $request->search_inventory_type_id);
        });

        $query->orderBy('products.product_name', 'asc')
              ->orderBy('inventories.created_at', 'desc')
              ->orderBy('inventories.updated_at', 'desc');

        return $query;
    }

    private function fetchStockledgerData($inventories)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);

        return $inventories->map(function ($inv)use ($quantityMaps) {
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            return [
                'product' => '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor: pointer; text-decoration: none;">' . ($inv->product->product_name ?? '') . '</a>',
                'category' => $inv->product->category->category_name ?? '',
                'our_product_name' => TruncateWithTooltipHelper::wrapText($inv->buyer_product_name),
                'specification' => TruncateWithTooltipHelper::wrapText(htmlspecialchars_decode($inv->specification, ENT_QUOTES)),
                'size' => TruncateWithTooltipHelper::wrapText($inv->size),
                'brand' => TruncateWithTooltipHelper::wrapText($inv->product_brand),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inv->inventory_grouping),
                'current_stock' => NumberFormatterHelper::formatQty($currentStockValue, $currencySymbol),
                'uom' => $inv->uom->uom_name ?? '',
            ];
        });
    }

    public function formatInventoryData($inventories)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();
        $quantityMaps = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);

        return $inventories->map(function ($inv) use ($quantityMaps) {
            $indentQty = $inv->indents->where('is_deleted', 2)->where('closed_indent', 2)->sum('indent_qty');
            $currentStockValue = StockQuantityHelper::calculateCurrentStockValue($inv->id,$inv->opening_stock,$quantityMaps);
            $currencySymbol = session('user_currency')['symbol'] ?? '₹';
            $showMinQty='';
            if(floatval(trim($inv->indent_min_qty)) && floatval(trim($currentStockValue)) < floatval(trim($inv->indent_min_qty))){
                $showMinQty= '<br><div style="position: relative; display: inline-block;">
                    <button class="ra-btn ra-btn-primary text-nowrap font-size-12 fw-normal px-2">
                        Min Qty
                    </button>
                    <span style="position: absolute; top: -15px; right: -10px; background-color: red; color: white; border-radius: 12px; padding: 2px 6px; font-size: 12px;">
                        '.$inv->indent_min_qty.'
                    </span>
                </div>';

            }
            return [
                'select' => $this->generateSelectHtml($inv->id,$indentQty,$this->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0),
                'product' => '<a href="' . route('buyer.report.productWiseStockLedger.index', ['id' => $inv->id]) . '" style="cursor: pointer; text-decoration: none;">' . ($inv->product->product_name ?? '') . '</a>'.$showMinQty,
                'category' => $inv->product->category->category_name ?? '',
                'our_product_name' => TruncateWithTooltipHelper::wrapText($inv->buyer_product_name),
                'specification' => TruncateWithTooltipHelper::wrapText(htmlspecialchars_decode($inv->specification, ENT_QUOTES)),
                'size' => TruncateWithTooltipHelper::wrapText($inv->size),
                'brand' => TruncateWithTooltipHelper::wrapText($inv->product_brand),
                'inventory_grouping' => TruncateWithTooltipHelper::wrapText($inv->inventory_grouping),
                'current_stock' => NumberFormatterHelper::formatQty($currentStockValue, $currencySymbol),
                'uom' => $inv->uom->uom_name ?? '',
                'indent_qty' => '<span id="total_indent_qty_' . $inv->id . '">' . NumberFormatterHelper::formatQty($indentQty, $currencySymbol) . '</span>',
                'rfq_qty' => ($qty = $this->getRfqData($inv->id)['rfq_qty'][$inv->id] ?? 0) > 0
                    ? '<span onclick="activeRfqPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;" >'.NumberFormatterHelper::formatQty($qty, $currencySymbol).'</span>'
                    : 0,
                'order_qty' => ($qty = $this->getOrderData($inv->id)['order_qty'][$inv->id] ?? 0) > 0
                    ? '<span onclick="orderDetailsPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;" >'.NumberFormatterHelper::formatQty($qty, $currencySymbol).'</span>'
                    : 0,
                // 'order_qty' => 0,
                'grn_qty' => '<span onclick="grnPopUP(' . $inv->id . ')" style="cursor:pointer;color:blue;">' . ($this->getGrnData($inv->id)['grn_qty'][$inv->id] ?? 0) . '</span>',

            ];
        });
    }
    public function getGrnData($inventoryId)
    {
        $grnQtySum = Grn::where('inventory_id', $inventoryId)
            ->where('grn_type', 1)
            ->where('inv_status', 1)
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
                          ->where('inventory_status', 1);
                } else {
                    $query->where('inventory_id', $inventoryIds)
                          ->where('inventory_status', 1);
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
            ->where('inventory_status', 1)
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


    //----------------------------------------INVENTORY MIN QTY LOGIC---------------------------------------------------------
    private function generateSelectHtml($inventoryId,$indentQty,$rfqQty = 0): string
    {
        // Generate the HTML for the select column (checkbox and collapse icons)
        return '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$inventoryId.'" class="pr-2 accordion_parent accordion_parent_'.$inventoryId.' close_indent_tds" tab-index="'.$inventoryId.'"><i class="bi bi-dash-lg"></i></span>
                <span data-toggle="collapse" style="cursor: pointer" id="plus_'.$inventoryId.'" class="pr-2 accordion_parent accordion_parent_'.$inventoryId.' open_indent_tds" tab-index="'.$inventoryId.'"><i class="bi bi-plus-lg"></i></span>
                <input type="checkbox" name="inv_checkbox[]" class="inventory_chkd" data-maxqty="' . ($indentQty - $rfqQty) . '" id="inventory_id_'.$inventoryId.'" value="'.$inventoryId.'">';
    }

    public static function getIdsByCategoryName(string $name): array
    {
        $startingWith = Category::where('category_name', 'like', '{$name}%')
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
        if (empty($startingWith)) {
            return Category::where('category_name', 'like', "%{$name}%")
                ->where('status', 1)
                ->pluck('id')
                ->toArray();
        }

        return $startingWith;
    }

    public function getInventoryDetails(Request $request)
    {
        $inventoryIds = $request->input('inventory_ids');

        if (empty($inventoryIds) || !is_array($inventoryIds)) {
            return response()->json([
                'status' => 0,
                'message' => 'No inventory IDs provided!',
            ]);
        }

        $inventories = Inventories::with(['product', 'uom'])
                ->whereIn('id', $inventoryIds)
                ->orderBy('created_at', 'desc')
                ->orderBy('updated_at', 'desc')
                ->get()
                ->sortBy(function($inventory) {
                    return $inventory->product->product_name ?? '';
                });


        if ($inventories->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'No inventory records found!',
            ]);
        }

        return response()->json([
            'status' => 1,
            'message' => 'Inventory details fetched successfully!',
            'data' => $inventories,
        ]);
    }

    //--------------------------------------------CURRENT STOCK REPORT---------------------------------------------------------
    public function currentStockGetData(Request $request)
    {
        if (!$request->ajax()) return;

        $query = $this->currentStockApplyFilters($request);
        $perPage = $request->length ?? 25;
        $page = intval(($request->start ?? 0) / $perPage) + 1;
        $paginated = $query->Paginate($perPage, ['*'], 'page', $page);
        $inventories = $paginated->items();

        $data1 = $this->formatCurrentStockData($inventories,true);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $paginated->total(),
            'recordsFiltered' => $paginated->total(),
            'data' => $data1,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
        ]);
    }
    public function exportCurrentStockData(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(3000);
        $currencySymbol = session('user_currency')['symbol'] ?? '₹';
        $filters = $request->only([
                'branch_id',
                'search_product_name',
                'search_category_id',
                'stock_qty',
                'from_date',
                'to_date',
            ]);

        $export = new CurrentStockExport($filters, $currencySymbol);
        $fileName = 'Current_Stock_Report_' . now()->format('d-m-Y') . '.xlsx';

        $response = $this->exportService->storeAndDownload($export, $fileName);

        return response()->json($response);
    }
    public function currentStockApplyFilters($request)
    {
        $userId = Auth::user()->parent_id ?: Auth::user()->id;

        $query = Inventories::with([
            'product:id,product_name,category_id',
            'product.category:id,category_name',
            'uom:id,uom_name'
        ])
        ->select('inventories.id','inventories.opening_stock','inventories.stock_price','inventories.specification','inventories.size', 'inventories.buyer_product_name','inventories.inventory_grouping','inventories.product_id', 'inventories.uom_id', 'inventories.buyer_branch_id', 'inventories.buyer_parent_id', 'inventories.is_indent', 'inventories.inventory_type_id', 'inventories.created_at', 'inventories.updated_at','products.product_name',
        DB::raw("(inventories.opening_stock
                + COALESCE(grn_sums.total,0)
                - COALESCE(issue_sums.total,0)
                + COALESCE(issue_return_sums.total,0)
                - COALESCE(stock_return_sums.total,0)
            ) AS current_stock_value"))
        ->join('products', 'products.id', '=', 'inventories.product_id')
        ->leftJoin(DB::raw("(SELECT inventory_id, SUM(grn_qty) AS total FROM grns WHERE company_id = {$userId} GROUP BY inventory_id) as grn_sums"), 'grn_sums.inventory_id', '=', 'inventories.id')
        ->leftJoin(DB::raw("(SELECT inventory_id, SUM(qty) AS total FROM issued WHERE buyer_id = {$userId} AND is_deleted = 2 GROUP BY inventory_id) as issue_sums"), 'issue_sums.inventory_id', '=', 'inventories.id')
        ->leftJoin(DB::raw("(SELECT inventory_id, SUM(qty) AS total FROM issued_returns WHERE buyer_id = {$userId} AND is_deleted = 2 GROUP BY inventory_id) as issue_return_sums"), 'issue_return_sums.inventory_id', '=', 'inventories.id')
        ->leftJoin(DB::raw("(SELECT inventory_id, SUM(qty) AS total FROM return_stocks WHERE buyer_id = {$userId} AND is_deleted = 2 GROUP BY inventory_id) as stock_return_sums"), 'stock_return_sums.inventory_id', '=', 'inventories.id')

        ->where('buyer_parent_id', $userId);

        $query->when($request->filled('stock_qty'), function ($q) use ($request) {
            if ($request->stock_qty == '0') {
                $q->having('current_stock_value', '=', 0);
            } elseif ($request->stock_qty == '1') {
                $q->having('current_stock_value', '>', 0);
            }
        });
        $query->when($request->filled(['from_date', 'to_date']), function ($q) use ($request) {
            $from = Carbon::createFromFormat('d-m-Y', $request->from_date)->startOfDay();
            $to = Carbon::createFromFormat('d-m-Y', $request->to_date)->endOfDay();

            $q->where(function ($subQuery) use ($from, $to) {
                $subQuery->whereBetween('inventories.created_at', [$from, $to])
                        ->orWhereBetween('inventories.updated_at', [$from, $to]);
            });
        });

        $query->when($request->filled('branch_id') && session('branch_id') != $request->branch_id, function () use ($request) {
            session(['branch_id' => $request->branch_id]);
        });

        $query->when($request->filled('branch_id'), fn($q) =>
            $q->where('buyer_branch_id', $request->branch_id));

        $query->when($request->filled('search_product_name'), fn($q) =>
            $q->whereHas('product', fn($q2) =>
                $q2->where('product_name', 'like', "%{$request->search_product_name}%")));

        $query->when($request->filled('search_category_id'), function ($q) use ($request) {
            $categoryIds = $this->getIdsByCategoryName($request->search_category_id);
                $q->whereHas('product.category', fn($qc) => $qc->whereIn('id', $categoryIds));
            });

        $query->when($request->filled('ind_non_ind'), function ($q) use ($request) {
            if ($request->ind_non_ind == 2) $q->where('is_indent', 1);
            if ($request->ind_non_ind == 3) $q->where('is_indent', 2);
        });

        $query->when($request->filled('search_inventory_type_id'), fn($q) =>
            $q->where('inventory_type_id', $request->search_inventory_type_id));

        return $query->orderBy('products.product_name', 'asc')
              ->orderBy('inventories.created_at', 'desc')
              ->orderBy('inventories.updated_at', 'desc');

    }

    private function formatCurrentStockData($inventories,$wrapText)
    {
        $inventories = collect($inventories);
        $inventoryIds = $inventories->pluck('id')->toArray();

        $qtyMap = StockQuantityHelper::preloadStockQuantityMaps($inventoryIds);
        $amountValMap = CurrentStockReportAmountHelper::preloadValueMaps($inventoryIds);

        return $inventories->map(function ($inv) use (&$serial_no,$wrapText,$qtyMap,$amountValMap) {

                $inv->specification=htmlspecialchars_decode($inv->specification, ENT_QUOTES);
                $stockQty = StockQuantityHelper::calculateCurrentStockValue($inv->id, $inv->opening_stock, $qtyMap);
                $stockAmountVal = CurrentStockReportAmountHelper::
                                    calculateAmountValue($inv->id, $inv->opening_stock, $inv->stock_price, $amountValMap);
                $currency = session('user_currency')['symbol'] ?? '₹';

            return [
                'product_name' => $inv->product->product_name ?? '',
                'our_product_name' => $wrapText ?
                                    TruncateWithTooltipHelper::wrapText($inv->buyer_product_name) : $inv->buyer_product_name,
                'specification' => $wrapText ? TruncateWithTooltipHelper::wrapText($inv->specification) : $inv->specification,
                'size' => $wrapText ? TruncateWithTooltipHelper::wrapText($inv->size) : $inv->size,
                'inventory_grouping' => $wrapText ?
                                        TruncateWithTooltipHelper::wrapText($inv->inventory_grouping) : $inv->inventory_grouping,
                'uom' => $inv->uom->uom_name ?? '',
                'current_stock_quantity' => NumberFormatterHelper::formatQty($stockQty,$currency),
                'total_amount' => NumberFormatterHelper::formatCurrency($stockAmountVal, $currency),
                'issued_quantity' =>NumberFormatterHelper::formatQty($qtyMap['issue'][$inv->id] ?? 0, $currency),
                'issued_amount' =>NumberFormatterHelper::formatCurrency($amountValMap['issue'][$inv->id] ?? 0, $currency),
                'grn_quantity' => NumberFormatterHelper::formatQty($qtyMap['grn'][$inv->id] ?? 0, $currency),
                'grn_amount' => NumberFormatterHelper::formatCurrency($amountValMap['grn'][$inv->id] ?? 0, $currency),
            ];
        });
    }
    //-------------------------------------------------------Delete Inventory--------------------------------------------------------
    public function deleteInventory(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid request type.'
            ]);
        }

        $inventoryIds = $request->input('inventory_ids');

        if (empty($inventoryIds) || !is_array($inventoryIds)) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory not found'
            ]);
        }

        // Check for processed inventories
        $hasProcessedInventories = Inventories::whereIn('id', $inventoryIds)
            ->where(function ($query) {
                $query->where('opening_stock', '>', 0)
                    ->orWhere('is_indent', 1);
            })
            ->exists();

        // Check if related manual orders or GRNs exist
        $hasManualOrders = ManualOrder::where('order_status', 1)
            ->whereHas('products', function ($query) use ($inventoryIds) {
                $query->whereIn('inventory_id', $inventoryIds);
            })
            ->exists();

        $hasGrns = Grn::whereIn('inventory_id', $inventoryIds)->exists();

        if ($hasProcessedInventories || $hasManualOrders || $hasGrns) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory already processed'
            ]);
        }

        // Delete the inventories
        $deleted = Inventories::whereIn('id', $inventoryIds)->delete();

        return response()->json([
            'status' => $deleted ? 1 : 0,
            'message' => $deleted ? 'Inventory deleted successfully' : 'Inventory not deleted, please try again later'
        ]);
    }
    //-------------------------------------------------Reset Indent------------------------------------------------------------------

    public function resetInventory(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json([
                'status' => 0,
                'message' => 'Invalid request type.'
            ]);
        }

        $inventoryIds = $request->input('inventory_ids');

        if (empty($inventoryIds) || !is_array($inventoryIds)) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory not found.'
            ]);
        }

        // Check if GRN exists
        if (Grn::whereIn('inventory_id', $inventoryIds)->exists()) {
            return response()->json([
                'status' => 0,
                'message' => 'GRN Already Processed.'
            ]);
        }

        // Check if Issued
        if (Issued::whereIn('inventory_id', $inventoryIds)->exists()) {
            return response()->json([
                'status' => 0,
                'message' => 'Issued Already Processed.'
            ]);
        }

        // Check if Return Stock exists
        if (ReturnStock::whereIn('inventory_id', $inventoryIds)->exists()) {
            return response()->json([
                'status' => 0,
                'message' => 'Stock Return Already Processed.'
            ]);
        }

        // Start Transaction
        try {
            DB::transaction(function () use ($inventoryIds) {
                // Mark indent as deleted
                Indent::whereIn('inventory_id', $inventoryIds)
                    ->update(['is_deleted' => 1]);

                // Reset RFQ's inventory_id
                RfqProductVariant::whereIn('inventory_id', $inventoryIds)
                    ->update(['inventory_id' => null]);
            });

            return response()->json([
                'status' => 1,
                'message' => 'Inventory reset successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory not reset, please try again later',
                'error' => $e->getMessage()
            ]);
        }
    }

    //----------------------------------------------------------Add RFQ-----------------------------------------------------------------
    public function fetchInventoryDetailsForAddRfq(Request $request)
    {
        $inventoryIds = $request->input('inventories');

        if (!$inventoryIds || !is_array($inventoryIds)) {
            return response()->json([
                'status' => 0,
                'message' => 'Inventory IDs are required',
            ]);
        }

        $inventories = Inventories::with(['product', 'uom'])
            ->whereIn('id', $inventoryIds)
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $inventories = $inventories->sortBy(function ($inventory) {
            return $inventory->product ? strtolower($inventory->product->product_name) : '';
        })->values();

        if ($inventories->isEmpty()) {
            return response()->json([
                'status' => 0,
                'message' => 'No inventories found',
            ]);
        }
        $rfqs = Rfq::with('rfqProductVariants')
            ->where('record_type', 2)
            ->whereHas('rfqProductVariants', function ($query) use ($inventoryIds) {
                $query->whereIn('inventory_id', $inventoryIds)->where('inventory_status', 1);
            })
            ->get();
        $rfqQuantities = [];
        foreach ($rfqs as $rfq) {
            $status = $rfq->buyer_rfq_status;
            if (!in_array($status, [8, 10])) {
                foreach ($rfq->rfqProductVariants as $variant) {
                    $inventoryId = $variant->inventory_id;
                    $qty = $variant->quantity;

                    if (!isset($rfqQuantities[$inventoryId])) {
                        $rfqQuantities[$inventoryId] = 0;
                    }

                    $rfqQuantities[$inventoryId] += $qty;
                }
            }
        }
        
        $data = $inventories->map(function ($inventory) use ($rfqQuantities) {
            $totalIndentQty = $inventory->indents()->sum('indent_qty');
            $rfqQty = $rfqQuantities[$inventory->id] ?? 0;
            $maxQty=$totalIndentQty - $rfqQty;
            return [
                'prod_name' => $inventory->product ? $inventory->product->product_name : null,
                'specification' => $inventory->specification,
                'size' => $inventory->size,
                'uom_name' => $inventory->uom ? $inventory->uom->uom_name : null,
                'id' => $inventory->id,
                'opening_stock' => $inventory->opening_stock,
                'total_indent_qty' => NumberFormatterHelper::formatQty($maxQty,session('user_currency')['symbol'] ?? '₹'),
                'maxQty' => $maxQty,
            ];
        });

        return response()->json([
            'status' => 1,
            'data' => $data,
        ]);
    }

    public function generateRFQ(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'rfq_qty' => 'required|array|min:1',
            'rfq_qty.*' => 'required|numeric|min:0.01',
            'inventory_id' => 'required|array',
            'inventory_id.*' => 'required|integer|exists:inventories,id',
        ], [
            'rfq_qty.*.required' => 'Quantity is mandatory against each product.',
            'rfq_qty.*.min' => 'Quantity must be at least 0.01.',
            'inventory_id.*.exists' => 'Invalid inventory item selected.',
        ]);

        $rfq_draft_id = 'D' . time() . rand(1000, 9999);
        $company_id = Auth::user()->parent_id ?: Auth::user()->id;
        $current_user_id = Auth::user()->id;

        DB::beginTransaction();

        try {
            $inv_pnr = '';
            $ind_remarks = [];
            foreach ($request->inventory_id as $index => $inventoryId) {
                $indents = Indent::where('inv_status', 1)
                        ->where('is_deleted', 2)
                        ->where('is_active', 1)
                        ->where('inventory_id', $inventoryId)
                        ->get(['id', 'inventory_unique_id', 'inventory_id', 'remarks']);                
                
                if ($indents->isNotEmpty()) {
                    $existing = $inv_pnr ? explode(',', $inv_pnr) : [];
                    $new = $indents->pluck('inventory_unique_id')->toArray();
                    $merged = array_merge($existing, $new);
                    sort($merged);
                    $inv_pnr = implode(',', $merged);

                    foreach ($indents as $indent) {
                        $ind_remarks[$indent->inventory_id][] = $indent->remarks;
                    }

                }
            }
            $rfq = new Rfq();
            $rfq->forceFill([
                "rfq_id" => $rfq_draft_id,
                "buyer_id" => $company_id,
                "buyer_user_id" => $current_user_id,
                "buyer_branch" => $request->branch_id,
                "record_type" => 1,
                "is_bulk_rfq" => 2,
                "buyer_rfq_status" => 1,
                "prn_no"=> $inv_pnr,                

            ]);
            $rfq->save();

            /***:- create temp product id  -:***/
            $addedProductIds = [];

            foreach ($request->inventory_id as $index => $inventoryId) {
                $qty = $request->rfq_qty[$index];

                // Load related data from inventory
                $inventory = Inventories::with(['product', 'uom'])->findOrFail($inventoryId);
                $product_id = $inventory->product_id;                

                // Add RFQ product (only once per product)
                if (!in_array($product_id, $addedProductIds)) {
                    RfqProduct::create([
                        "rfq_id" => $rfq->rfq_id,
                        "product_id" => $product_id,
                        "product_order" => 1,
                        "remarks" => !empty(array_filter($ind_remarks[$inventoryId] ?? []))
                            ? implode(',', array_filter($ind_remarks[$inventoryId]))
                            : '',
                    ]);
                    $addedProductIds[] = $product_id;
                }
                $variant = new RfqProductVariant();
                $variant->forceFill([
                    "rfq_id" => $rfq->rfq_id,
                    "product_id" => $product_id,
                    "variant_order" => 1,
                    "variant_grp_id" => now()->timestamp . mt_rand(10000, 99999),
                    "uom" => $inventory->uom_id ?? '',
                    "size" => $inventory->size ?? '',
                    "quantity" => $qty,
                    "specification" => $inventory->specification ?? '',
                    "inventory_id" => $inventoryId
                ]);
                $variant->save();
                // $this->updateInventory($product_id,$inventory->specification,$inventory->size,$inventory->uom_id,$qty,$company_id,$request->branch_id,$rfq->rfq_id);
            }
            

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ generated successfully.',
                'rfq_id' => $rfq->rfq_id,
                'url' => route('buyer.rfq.compose-draft-rfq', ['draft_id' => $rfq->rfq_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to create RFQ Draft. ' . $e->getMessage(),
            ]);
        }
    }

    //------------------------------------------------------Active RFQ Details---------------------------------------------------
    public function getActiveRfqDetails($inventoryId)
    {
        try {
            $rfqs = Rfq::with('rfqProductVariants')
                        ->whereHas('rfqProductVariants', function ($query) use ($inventoryId) {
                            $query->where('inventory_id', $inventoryId)->where('inventory_status', 1);
                        })                      
                        ->where('record_type', 2)
                        ->orderBy('updated_at', 'asc') 
                        ->get();

            $rfqData = $rfqs->map(function ($rfq) use ($inventoryId) {
                $filteredVariants = $rfq->rfqProductVariants->where('inventory_id', $inventoryId);
                $totalQty = $filteredVariants->sum('quantity');
                return [
                    'rfq_no'         => $rfq->rfq_id,
                    'rfq_date'       => optional($rfq->updated_at)->format('d-m-Y'),
                    'rfq_closed'     => in_array($rfq->buyer_rfq_status, [8, 10]) ? 'Yes' : 'No',
                    'rfq_qty'        => NumberFormatterHelper::formatQty($totalQty,session('user_currency')['symbol'] ?? '₹'),
                    'rfq_id'         => $rfq->rfq_id,
                ];
            });

            return response()->json([
                'status' => 1,
                'data' => $rfqData,
                'message' => 'RFQ Active Details Succesfully Fetched Against This Inventory!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch RFQ details: ' . $e->getMessage()
            ], 500);
        }
    }
    //------------------------------------------------------Order Details---------------------------------------------------
    public function getOrderDetails($inventoryId)
    {
        try {
            $data = []; 

            $rfqs = Rfq::with([
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
                    $query->where('inventory_id', $inventoryId);
                })
                ->get();

            foreach ($rfqs as $rfq) {
                foreach ($rfq->orders as $order) {
                    $orderNo = $order->po_number;
                    $orderDate = $order->created_at ? $order->created_at->format('d-m-Y') : null;
                    $vendorName = $order->vendor->legal_name ?? 'N/A';
                    $rfqNo = $rfq->rfq_id; 

                    foreach ($order->order_variants as $ov) {
                        foreach ($rfq->rfqProductVariants as $variant) {
                            if ($variant->product_id == $ov->product_id) {
                                $data[] = [
                                    'order_no'    => $orderNo,
                                    'rfq_no'      => $rfqNo,
                                    'order_date'  => $orderDate,
                                    'order_qty'   => $ov->order_quantity,
                                    'vendor_name' => $vendorName,
                                ];
                            }
                        }
                    }
                }
            }

            if (empty($data)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'No orders found for this inventory.'
                ]);
            }

            return response()->json([
                'status' => 1,
                'data' => $data,
                'message' => 'Order details successfully fetched for this inventory.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Failed to fetch order details: ' . $e->getMessage()
            ], 500);
        }
    }


    //------------------------------------------------------Update Inventory---------------------------------------------------
    public function updateInventory($productId, $specification, $size, $uomId, $qty, $companyId, $branchId,$rfqId)
    {
        DB::beginTransaction();

        try {
            $spec = strtolower(trim($specification));
            $sz   = strtolower(trim($size));

            // Check or create inventory
            $inventory = Inventories::firstOrCreate([
                'product_id'    => $productId,
                'specification' => $spec,
                'size'          => $sz,
                'buyer_branch_id'     => $branchId,
                'buyer_parent_id'    => $companyId,
            ], [
                'uom_id'           => $uomId,
                'opening_stock' => 0,
                'is_indent'     => 0,
                'created_by'    => Auth::user()->id,
                'created_at'    => now(),
            ]);

            //update inventory id
            RfqProductVariant::where('product_id', $productId)
                ->where('specification', $spec)
                ->where('size', $sz)
                ->where('rfq_id', $rfqId)
                ->update(['inventory_id' => $inventory->id]);


            //Get open RFQ quantity (status not in 8, 10)
            $openRfqQty = RfqProductVariant::where('inventory_id', $inventory->id)
                            ->where('inventory_status', 1)
                        // ->where('rfq_id', $rfqId)
                        ->whereHas('rfq', function ($q) {
                            $q->whereNotIn('buyer_rfq_status', [8, 10])
                            ->where('record_type', 2);
                        })
                        ->sum('quantity');


            // Get existing indent quantity
            $existingIndentQty = Indent::where('inventory_id', $inventory->id)
                ->where('closed_indent', 2)
                ->where('is_deleted', 2)
                ->sum('indent_qty');

            // $totalCommitted = $openRfqQty + $existingIndentQty;

            // Insert indent if shortfall
            // if ($totalCommitted < $qty) {
            if ($existingIndentQty < $openRfqQty) {
                // $indentQty = $qty - $totalCommitted;
                $indentQty = $openRfqQty - $existingIndentQty;

                $maxIndentId = Indent::where('buyer_id', $companyId)->max('inventory_unique_id') ?? 0;

                Indent::create([
                    'buyer_id'          => $companyId,
                    'inventory_unique_id'   => $maxIndentId + 1,
                    'inventory_id'        => $inventory->id,
                    'closed_indent'        => 2,
                    'is_deleted'        => 2,
                    'indent_qty'          => $indentQty,
                    'created_by'          => auth()->id(),
                    'updated_by'     => auth()->id(),
                    'updated_date'   => now(),
                ]);

                $inventory->update(['is_indent' => 1]);
            }

            DB::commit();
            return $inventory->id;

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }


}
