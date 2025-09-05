<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\BranchDetail;
use App\Models\Buyer;
use App\Models\IndentApi;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendor;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IndentApiExport;

class ApiIndentController extends Controller
{

    public $new_key;
    public $old_key;
    public function __construct()
    {

        /***:- set the api keys  -:***/
        $this->new_key = 'N12102024f53f4aaf642532a2fbd27ad2e77d67f9867b4a04fcdd12dec2024';
        $this->old_key = 'd6210c40ac53f4aaf642532a2fbd27ad4a7ba2e77d67f9867b4a04fcddcdae';
    }



    public function postmanStore(Request $request, $key)
    {

        // dd($key,$this->new_key,$this->old_key,$this->old_key == $key);
        /***:- validate the api key from the params  -:***/
        if (($this->new_key == $key) || ($this->old_key === $key)) {
            // return response()->json(['message' => 'Invalid token key.'], 400);
        } else {
            return response()->json(['message' => 'Invalid token key.'], 400);
        }

        $validateData = [];

        if ($this->new_key == $key) {
            /***:- validate the new user data from the array  -:***/
            $validateData = [
                '*.email'       => 'required|exists:users,email',
                '*.PR_GSTN'     => 'required|exists:buyers,gstin',
                '*.PR_Text'     => 'required|string|max:255',
                '*.PR_Number'   => 'required|string|min:1',
                '*.PR_Qty'      => 'required|numeric|min:1',
                '*.PR_Material' => 'required|string|max:255',
                '*.PR_Specs'    => 'nullable|string|max:255',
                '*.PR_Size'     => 'nullable|string|max:255',
            ];
        } elseif ($this->old_key == $key) {
            /***:- validate the old user data from the array  -:***/
            $validateData = [
                'email'       => 'required|exists:users,email',
                '*entity_code'  => 'required',
                '*entity_gst'  => 'required|exists:buyers,gstin',
                '*indent_number'  => 'required',
                '*user_name'  => 'required',
                '*products'  => 'required',
                'products.*.product_id'     => 'required',
                'products.*.product_name'     => 'required|string|max:255',
                'products.*.quantity'     => 'required|numeric|min:1',
            ];
        }

        $validator = Validator::make($request->all(), $validateData);

        /***:- return invalid data  -:***/
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ]);
        }



        if ($this->new_key == $key) {
            /***:- get the filter data new user -:***/
            $data = $this->requestNewUserData($request);
        } elseif ($this->old_key == $key) {
            /***:- get the filter data old user -:***/
            $data = $this->requestOldUserData($request);
        }




        /***:- bulk insert data  -:***/
        $createIndent = IndentApi::insert($data);

        return response()->json(['status' => true, 'message' => 'Product Inserted successfully.']);
    }

    public function requestNewUserData($request)
    {
        $not_inserted_count =   0;
        $inserted_count     =   0;
        $i                  =   0;
        $ins_data           =   array();
        $data               = $request->all();
        try {
            if (isset($data[0]['PR_GSTN']) && !empty($data[0]['PR_GSTN'])) {

                $apigst = isset($data[0]['PR_GSTN']) ? $data[0]['PR_GSTN'] : '';

                /***:- get buyer info with the gst no.  -:***/
                $buyer_info    =  Buyer::where('gstin', $apigst)->first();

                if (isset($buyer_info) && !empty($buyer_info)) {
                    foreach ($data as $key => $val) {
                        if (isset($val['PR_Number']) && !empty($val['PR_Number']) && isset($val['PR_Material']) && !empty($val['PR_Material']) && isset($val['PR_Text']) && !empty($val['PR_Text']) && isset($val['PR_Qty']) && !empty($val['PR_Qty']) && isset($val['PR_UOM'])) {
                            $check_indent_product_no = true;
                            if ($check_indent_product_no) {

                                /***:- get uom name  -:***/
                                $uom_data = Uom::where('uom_name', $val['PR_UOM'])->first();

                                $ourproduct_details  =  Product::where('product_name', $val['PR_Text'])->where('status', 1)->first();

                                /***:- get product name from product alias  -:***/
                                if (!$ourproduct_details) {
                                    $alias = ProductAlias::whereLike('alias', $val['PR_Text'])->first();
                                    if ($alias) {
                                        $ourproduct_details = Product::where('id', $alias->product_id)->where('status', 1)->first();
                                    }
                                }

                                /***:- get branch info
                                 * default branch first entry
                                 * TODO need to add default branch column
                                 *   -:***/

                                $buyer_branch = $this->getDefaultBranch($buyer_info->user_id);

                                $ins_data[$i]['buyer_id']               =    $buyer_info->user_id ?? 0;
                                $ins_data[$i]['branch_id']              =   $buyer_branch->branch_id ?? 0;
                                $ins_data[$i]['indent_no']              =   substr($val['PR_Number'], 0, 50);
                                $ins_data[$i]['entity_code']            =   isset($val['entity_code']) && $val['entity_code'] != '' ? substr($val['entity_code'], 0, 200) : null;
                                $ins_data[$i]['user_name']              =   isset($val['user_name']) && $val['user_name'] != '' ? substr($val['user_name'], 0, 100) : null;
                                $ins_data[$i]['product_srno']           =   isset($val['slno_new']) && $val['slno_new'] != '' ? $val['slno_new'] : 0;
                                $ins_data[$i]['division_code']          =   isset($val['division_code_new']) && $val['division_code_new'] != '' ? substr($val['division_code_new'], 0, 10) : null;
                                $ins_data[$i]['dept_code']              =   isset($val['dept_code_new']) && $val['dept_code_new'] != '' ? $val['dept_code_new'] : null;
                                $ins_data[$i]['cost_code']              =   isset($val['cost_code_new']) && $val['cost_code_new'] != '' ? $val['cost_code_new'] : null;
                                $ins_data[$i]['product_id']             =   $val['PR_Material'];
                                $ins_data[$i]['product_name']           =   $val['PR_Text'];
                                $ins_data[$i]['product_specs']          =   isset($val['PR_Specs']) && $val['PR_Specs'] != '' ? e(substr($val['PR_Specs'], 0, 2900)) : null;
                                $ins_data[$i]['product_size']           =   isset($val['PR_Size']) && $val['PR_Size'] != '' ? e(substr($val['PR_Size'], 0, 1450)) : null;
                                $ins_data[$i]['product_brand']          =   isset($val['product_brand']) && $val['product_brand'] != '' ? substr($val['product_brand'], 0, 100) : null;
                                $ins_data[$i]['quantity']               =   $val['PR_Qty'];
                                $ins_data[$i]['uom']                    =   $uom_data->id ?? 0;
                                $ins_data[$i]['remarks']                =   isset($val['remarks_new']) ? e(substr($val['remarks_new'], 0, 2900)) : '';
                                $ins_data[$i]['match_product_name']     =   $ourproduct_details->product_name ?? '';
                                $ins_data[$i]['match_product_id']       =   $ourproduct_details->id ?? 0;
                                $ins_data[$i]['erp_stock']              =   isset($val['erp_stock_new']) && $val['erp_stock_new'] != '' ? $val['erp_stock_new'] : 0;
                                $ins_data[$i]['pending_po_qty']         =   isset($val['pending_po_qty_new']) && $val['pending_po_qty_new'] != '' ? $val['pending_po_qty_new'] : 0;
                                $ins_data[$i]['last_six_month_consump'] =   isset($val['last_six_month_consump_new']) && $val['last_six_month_consump_new'] != '' ? $val['last_six_month_consump_new'] : 0;
                                $ins_data[$i]['last_po_vrno']           =   isset($val['last_po_vrno_new']) && $val['last_po_vrno_new'] != '' ? $val['last_po_vrno_new'] : null;
                                $ins_data[$i]['last_po_vrdate']         =   isset($val['last_po_vrdate_new']) && $val['last_po_vrdate_new'] != '' ? $val['last_po_vrdate_new'] : null;
                                $ins_data[$i]['last_po_qty']            =   isset($val['last_po_qty_new']) && $val['last_po_qty_new'] != '' ? $val['last_po_qty_new'] : 0;
                                $ins_data[$i]['last_po_rate']           =   isset($val['last_po_rate_new']) && $val['last_po_rate_new'] != '' ? $val['last_po_rate_new'] : 0;
                                $ins_data[$i]['delivery_date']          =   isset($val['PR_Item_Del_Date']) && $val['PR_Item_Del_Date'] != '' ? $val['PR_Item_Del_Date'] : null;
                                $ins_data[$i]['material_group']         =   isset($val['PR_Mat_Group']) && $val['PR_Mat_Group'] != '' ? substr($val['PR_Mat_Group'], 0, 100) : null;
                                $ins_data[$i]['purchase_group']         =   isset($val['PR_Purchase_Group']) && $val['PR_Purchase_Group'] != '' ? substr($val['PR_Purchase_Group'], 0, 100) : null;
                                $ins_data[$i]['plant']                  =   isset($val['PR_Plant']) && $val['PR_Plant'] != '' ? substr($val['PR_Plant'], 0, 100) : null;
                                $ins_data[$i]['template_type']          =   2;
                                $i++;
                                $inserted_count++;
                            } else {
                                $not_inserted_count++;
                                break;
                            }
                        } else {
                            $not_inserted_count++;
                        }
                    }
                    if (isset($ins_data) && !empty($ins_data) && $not_inserted_count == 0) {

                        // dd($ins_data);
                        return $ins_data;
                    } else {
                        $message = 'Product Not Inserted';
                        logger()->error($message);
                        throw $message;
                    }
                } else {
                    $message = 'This API facility is not available on the system.';

                    logger()->error($message);
                    throw  $message;
                }
            } else {

                $message = 'GSTN Required.';
                logger()->error($message);
                throw $message;
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function requestOldUserData($request)
    {


        $not_inserted_count =   0;
        $inserted_count     =   0;
        $i                  =   0;
        $ins_data           =   array();
        $data               = $request->all();


        try {
            //dd($data);
            if (isset($data['entity_code']) && isset($data['indent_number']) && !empty($data['indent_number']) && isset($data['user_name']) && isset($data['products']) && !empty($data['products'])) {

                $apigst = isset($data['entity_gst']) ? $data['entity_gst'] : '';

                /***:- get buyer info with the gst no.  -:***/
                $buyer_info    =  Buyer::where('gstin', $apigst)->first();
                // dd($buyer_info);
                if (isset($buyer_info) && !empty($buyer_info)) {
                    foreach ($data['products'] as $key => $val) {
                        $check_indent_product_no = true;
                        if ($check_indent_product_no) {

                            /***:- get uom name  -:***/
                            $uom_data = Uom::where('uom_name', $val['uom'])->first();

                            $ourproduct_details  =  Product::where('product_name', $val['product_name'])->where('status', 1)->first();

                            /***:- get product name from product alias  -:***/
                            if (!$ourproduct_details) {
                                $alias = ProductAlias::whereLike('alias', $val['product_name'])->first();
                                if ($alias) {
                                    $ourproduct_details = Product::where('id', $alias->product_id)->where('status', 1)->first();
                                }
                            }

                            /***:- get branch info
                             * default branch first entry
                             * TODO need to add default branch column
                             *   -:***/

                            $buyer_branch = $this->getDefaultBranch($buyer_info->user_id);

                            $ins_data[$i]['buyer_id']               =    $buyer_info->user_id ?? 0;
                            $ins_data[$i]['branch_id']              =   $buyer_branch->branch_id ?? 0;


                            $ins_data[$i]['indent_no']              =   substr($data['indent_number'], 0, 50);
                            $ins_data[$i]['entity_code']            =   isset($data['entity_code']) && $data['entity_code'] != '' ? substr($data['entity_code'], 0, 200) : NULL;
                            $ins_data[$i]['user_name']              =   isset($data['user_name']) && $data['user_name'] != '' ? substr($data['user_name'], 0, 200) : NULL;

                            $ins_data[$i]['product_srno']           =   isset($val['slno']) && $val['slno'] != '' ? $val['slno'] : 0;
                            $ins_data[$i]['division_code']          =   isset($val['division_code']) && $val['division_code'] != '' ? substr($val['division_code'], 0, 10) : NULL;
                            $ins_data[$i]['dept_code']              =   isset($val['dept_code']) && $val['dept_code'] != '' ? substr($val['dept_code'], 0, 10) : NULL;
                            $ins_data[$i]['cost_code']              =   isset($val['cost_code']) && $val['cost_code'] != '' ? substr($val['cost_code'], 0, 10) : NULL;
                            $ins_data[$i]['product_id']             =   $val['product_id'];
                            $ins_data[$i]['product_name']           =   $val['product_name'];
                            $ins_data[$i]['product_specs']          =   isset($val['product_specs']) && $val['product_specs'] != '' ? e(substr($val['product_specs'], 0, 2900), 'encode') : NULL;
                            $ins_data[$i]['product_size']           =   isset($val['product_size']) && $val['product_size'] != '' ? e(substr($val['product_size'], 0, 1450), 'encode') : NULL;
                            $ins_data[$i]['product_brand']          =   isset($val['product_brand']) && $val['product_brand'] != '' ? substr($val['product_brand'], 0, 100) : NULL;
                            $ins_data[$i]['quantity']               =   $val['quantity'];
                            $ins_data[$i]['uom']                    =   $uom_data->id ?? 0;
                            $ins_data[$i]['remarks']                =   e(substr($val['remarks'], 0, 2900), 'encode');
                            $ins_data[$i]['match_product_name']     =   $ourproduct_details->product_name ?? '';
                            $ins_data[$i]['match_product_id']       =   $ourproduct_details->id ?? 0;
                            $ins_data[$i]['erp_stock']              =   isset($val['erp_stock']) && $val['erp_stock'] != '' ? $val['erp_stock'] : 0;
                            $ins_data[$i]['pending_po_qty']         =   isset($val['pending_po_qty']) && $val['pending_po_qty'] != '' ? $val['pending_po_qty'] : 0;
                            $ins_data[$i]['last_six_month_consump'] =   isset($val['last_six_month_consump']) && $val['last_six_month_consump'] != '' ? $val['last_six_month_consump'] : 0;
                            $ins_data[$i]['last_po_vrno']           =   isset($val['last_po_vrno']) && $val['last_po_vrno'] != '' ? substr($val['last_po_vrno'], 0, 50) : NULL;
                            $ins_data[$i]['last_po_vrdate']         =   isset($val['last_po_vrdate']) && $val['last_po_vrdate'] != '' ? $val['last_po_vrdate'] : NULL;
                            $ins_data[$i]['last_po_qty']            =   isset($val['last_po_qty']) && $val['last_po_qty'] != '' ? $val['last_po_qty'] : 0;
                            $ins_data[$i]['last_po_rate']           =   isset($val['last_po_rate']) && $val['last_po_rate'] != '' ? $val['last_po_rate'] : 0;
                            $ins_data[$i]['template_type']          =   1;
                            $i++;
                            $inserted_count++;
                        } else {
                            $not_inserted_count++;
                            break;
                        }
                    }

                    if (isset($ins_data) && !empty($ins_data) && $not_inserted_count == 0) {
                        return $ins_data;
                    } else {
                        logger()->error('Product Not Inserted');
                        throw 'Product Not Inserted';
                    }
                } else {
                    logger()->error('This API facility is not available on the system.');
                    throw 'This API facility is not available on the system.';
                }
            } else {
                logger()->error('GSTN Required.');
                throw 'GSTN Required.';
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            throw $th;
        }
    }


    public function apiIndentQuery($request)
    {
        $query = IndentApi::with(['getProduct', 'getUom'])->where('buyer_id', Auth::user()->id);

        if ($request->filled('product_name')) {
            /*$query->whereHas('getProduct', function ($q) use ($request) {
                $q->where('product_name', 'like', '%' . $request->product_name . '%');
            });*/
            $query->where('product_name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->filled('branch_id')) {
            $branch_id = $request->input('branch_id');
            $request->session()->put('branch_id', $branch_id);
            $query->where('branch_id', $branch_id);
        } else {
            $defaultBranch = $this->getDefaultBranch(Auth::user()->id);
            $request->session()->put('branch_id', $defaultBranch->branch_id);
        }

        if ($request->filled('product_id')) {
            $query->whereLike(['product_id'], $request->product_id);
        }

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->input('ids', []));
        }

        if ($request->filled('indent_no')) {
            $query->whereLike(['indent_no'], $request->indent_no);
        }

        if ($request->filled('plant')) {
            $query->where('plant', 'like', '%' . $request->plant . '%');
        }

        if ($request->filled('division_code')) {
            $query->where('division_code', 'like', '%' . $request->division_code . '%');
        }

        return $query;
    }


    public function apiIndentList1(Request $request)
    {
        // dd(15);
        /***:- clone the query  -:***/
        $query = clone $this->apiIndentQuery($request);
        $indentData = $query->orderBy('id', 'desc')->paginate(50);

        $indentData->getCollection()->transform(function ($indent) {
            $indent->rfq_quantity_sum = $indent->getRfqQuantitySum();
            return $indent;
        });

        $branches = BranchDetail::getDistinctActiveBranchesByUser(Auth::user()->id);

        $view_template = 1;
        if (count($indentData) > 0) {
            $view_template = $indentData[0]->template_type;
        }
        return view('buyer.api-indent.index', compact('indentData', 'branches', 'view_template'));
    }



    public function apiIndentList(Request $request)
    {

        $branches = BranchDetail::getDistinctActiveBranchesByUser(Auth::user()->id);
        $query = clone $this->apiIndentQuery($request);
        $indentData = $query->orderBy('id', 'desc')->first();
        $view_template = 1;
        if ($indentData) {
            $view_template = $indentData->template_type;
        }


        if ($request->ajax()) {
            $query = clone $this->apiIndentQuery($request)->orderBy('id', 'desc');

            // Search support
            if ($request->has('search') && $request->search['value']) {
                $search = $request->search['value'];
                $query->where(function ($q) use ($search) {
                    $q->where('indent_no', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%")
                        ->orWhere('product_id', 'like', "%{$search}%");
                });
            }

            // Pagination
            $length = $request->input('length', 50);
            $start = $request->input('start', 0);
            $page = ($start / $length) + 1;

            $paginator = $query->paginate($length, ['*'], 'page', $page);

            $view_template = count($paginator) > 0 ? $paginator[0]->template_type : 1;

            $data = $paginator->getCollection()->transform(function ($indent) {
                $indent->rfq_quantity_sum = $indent->getRfqQuantitySum();
                return $indent;
            })->map(function ($indent) use ($view_template) {

                // Checkbox column
                $checkbox = $indent->match_product_id
                    ? '<div class="form-check">
                   <input class="product-checkbox" type="checkbox" name="indent-checkbox"
                        data-id="' . $indent->id . '" value="' . $indent->id . '" />
               </div>'
                    : '';

                // RFQ quantity link
                $rfqQty = $indent->rfq_quantity_sum
                    ? '<span class="text-primary-blue text-decoration-underline cursor-pointer rfqList"
                     data-indent_id="' . $indent->id . '" data-bs-toggle="modal"
                     data-bs-target="#activeRfqDetailsModal">' . $indent->rfq_quantity_sum . '</span>'
                    : '0';

                // Product block form (kept same as original)
                $productBlock = view('buyer.api-indent.product-block', compact('indent'))->render();

                if ($view_template == 1) {
                    return [
                        'id' => $indent->id,
                        'checkbox' => $checkbox,
                        'indent_no' => $indent->indent_no,
                        'product_name' => $indent->product_name,
                        'product_id' => $indent->product_id,
                        'division_code' => $indent->division_code,
                        'dept_code' => $indent->dept_code,
                        'cost_code' => $indent->cost_code,
                        'product_block' => $productBlock,
                        'product_specs' => $indent->product_specs,
                        'product_size' => $indent->product_size,
                        'uom' => optional($indent->getUom)->uom_name,
                        'product_brand' => $indent->product_brand,
                        'created_at' => optional($indent->created_at)->format('d-m-Y'),
                        'quantity' => $indent->quantity,
                        'rfq_quantity_sum' => $rfqQty,
                    ];
                } else {
                    return [
                        'id' => $indent->id,
                        'checkbox' => $checkbox,
                        'indent_no' => $indent->indent_no,
                        'product_id' => $indent->product_id,
                        'plant' => $indent->plant,
                        'product_name' => $indent->product_name,
                        'quantity' => $indent->quantity,
                        'uom' => optional($indent->getUom)->uom_name,
                        'delivery_date' => $indent->delivery_date,
                        'material_group' => $indent->material_group,
                        'purchase_group' => $indent->purchase_group,
                        'product_block' => $productBlock,
                        'product_specs' => $indent->product_specs,
                        'product_size' => $indent->product_size,
                        'created_at' => optional($indent->created_at)->format('d-m-Y'),
                        'rfq_quantity_sum' => $rfqQty,
                    ];
                }
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $paginator->total(),
                'recordsFiltered' => $paginator->total(),
                'data' => $data,
                'view_template' => $view_template
            ]);
        }

        return view('buyer.api-indent.index', compact('branches', 'view_template'));
    }

    public function exportIndentData(Request $request)
    {
        /***:- clone the query  -:***/
        $query = clone $this->apiIndentQuery($request);
        $indentData = $query->orderBy('id', 'desc')->get();
        $indentData->transform(function ($indent) {
            $indent->rfq_quantity_sum = $indent->getRfqQuantitySum();
            return $indent;
        });

        $view_template = 1;
        if (count($indentData) > 0) {
            $view_template = $indentData[0]->template_type;
        }

        return Excel::download(new IndentApiExport($indentData, $view_template), 'API Inventory Reports ' . date('d-m-Y') . '.xlsx');
    }



    public function updateIndentApi(Request $request)
    {
        /***:- update indent  -:***/
        IndentApi::where('id', $request->get('indent_id'))->where('buyer_id', Auth::user()->id)
            ->update(['match_product_name' => $request->get('product_name'), 'match_product_id' => $request->get('product_id')]);

        return redirect()->back()->with('Data update successfully.');
    }


    public function deleteIndent(Request $request)
    {
        if (!$request->has('ids') || !is_array($request->ids)) {
            return response()->json(['status' => 'error', 'message' => 'No records selected']);
        }

        IndentApi::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => 'success']);
    }


    public function getRfqList(Request $request)
    {
        $products = RfqProductVariant::with(['rfq'])->where('api_id', $request->id)
            ->whereHas('rfq', function ($q) {
                // $q->whereNotIn('buyer_rfq_status', ['8', '10']);
            })->get()
            ->map(function ($products) {
                if ($products->rfq) {
                    $products->rfq->formatted_created_at  = $products->rfq->created_at->format('d-m-Y');
                }
                if ($products->created_at) {
                    $products->formatted_created_at  = $products->created_at->format('d-m-Y');
                }
                return $products;
            });
        return response()->json($products);
    }


    public function getProductList(Request $request)
    {
        $products = IndentApi::whereIn('id', $request->ids)->get();
        return response()->json($products);
    }

    public function getDefaultBranch($user_id)
    {
        $buyer_branch = BranchDetail::select('id', 'branch_id', 'name')
            ->where("user_id", $user_id)
            ->where('status', 1)
            ->orderBy('id', 'asc')
            ->first();

        return $buyer_branch;
    }

    public function generateRFQ(Request $request)
    {



        /*$vendors_id = $request->vendors_id;

        if (count($vendors_id)<=0) {
            return response()->json([
                'status' => false,
                'message' => 'Please select at least one vendor to Generate RFQ'
            ]);
        }*/

        $rfq_draft_id = 'D' . time() . rand(1000, 9999);
        // $rfq_draft_date = date('Y-m-d H:i:s');
        $company_id = getParentUserId();
        $current_user_id = Auth::user()->id;

        DB::beginTransaction();

        try {


            $data = $request->all();
            $branchId = $request->input('branchId');

            $rfq = Rfq::create([
                "rfq_id" => $rfq_draft_id,
                "buyer_id" => $company_id,
                "buyer_user_id" => $current_user_id,
                "buyer_branch" => $branchId,
                "record_type" => 1,
                "is_bulk_rfq" => 2,
                "buyer_rfq_status" => 1,
                "is_api_request" => 1
            ]);


            /***:- create temp product id  -:***/
            $tempProdId = 0;

            foreach ($data['rfq'] as $key => $indent) {

                $product_id = $indent['match_product_id'];

                if ($tempProdId != $product_id) {
                    RfqProduct::create([
                        "rfq_id" => $rfq->rfq_id,
                        "product_id" => $product_id,
                        "product_order" => 1,
                    ]);
                }


                $variant_grp_id = now()->timestamp . mt_rand(10000, 99999);

                RfqProductVariant::create([
                    "rfq_id" => $rfq->rfq_id,
                    "product_id" => $product_id,
                    "variant_order" => 1,
                    "variant_grp_id" => $variant_grp_id,

                    "uom" => $indent['uom'] ?? "",
                    "size" => $indent['product_size'] ?? "",
                    "quantity" => $indent['qty'],
                    "specification" => $indent['product_specs'] ?? "",
                    "api_id" => $indent['indent_api_id']
                ]);

                /***:- swap the current product id -:***/
                $tempProdId = $product_id;
            }

            DB::commit();

            //return redirect()->route('buyer.rfq.compose-draft-rfq', ['draft_id' => $rfq->rfq_id])->with('success', 'Draft RFQ created successfully.');

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
                'complete_message' => $e
            ]);
        }
    }
}