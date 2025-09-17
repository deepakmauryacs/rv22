<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BranchDetail;
use App\Models\Uom;
use App\Models\InventoryType;
use App\Models\Product;
use App\Models\TempInventoryMgt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Traits\TrimFields;
class BulkInventoryController extends Controller
{
    use TrimFields;
    public function importBulkInventory()
    {
        $data['page_heading'] = "Import Bulk Inventory";
        session(['page_title' => 'Import Bulk Inventory - Raprocure']);
        $userId = Auth::user()->id;
        DB::table('temp_inventory_mgt')->where('user_id', $userId)->delete();
        $data['branch_data'] = BranchDetail::getDistinctActiveBranchesByUser(Auth::user()->id);
        $data['uom_list'] = Uom::all();

        return view('buyer.bulkinventory.bulk_inventory', $data);
    }
    
    public function uploadCSV(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'import_product' => 'required|file|mimes:csv,txt|mimetypes:text/plain,text/csv,application/csv',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => 'Invalid file uploaded.']);
        }

        $file = $request->file('import_product');

        // Memory-efficient CSV parsing
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json(['status' => 2, 'message' => 'Unable to read CSV file']);
        }

        $headers = fgetcsv($handle);
        if (!$headers || count($headers) < 3) {
            return response()->json(['status' => 2, 'message' => 'CSV header is invalid or missing']);
        }

        $headers = array_map(fn($h) => str_replace(' ', '_', trim($h)), $headers);

        $uomList = $this->getUomMappings();
        $inventoryTypes = $this->getInventoryTypeMappings();

        $productNames = [];
        $rows = [];
        $rowCount = 0;

        // Phase 1: Read CSV line-by-line
        while (($row = fgetcsv($handle)) !== false) {
            $assocRow = array_combine($headers, $row);
            if (!$assocRow || !isset($assocRow['Product_Name'])) continue;

            $productName = Str::of($assocRow['Product_Name'])->squish()->__toString();
            $assocRow['Product_Name'] = $productName;
            $productNames[$productName] = true;
            $rows[] = $assocRow;
            $rowCount++;
        }

        fclose($handle);

        if (empty($rows)) {
            return response()->json(['status' => 2, 'message' => 'CSV file is empty']);
        }

        // Fetch verified products only once
        $finalProductData = [];
        foreach (array_keys($productNames) as $name) {
            $finalProductData[$name] = $this->verify_product_for_supplier_new($name, true);
        }

        $response_data = [];
        $prod_name_arr = [];

        // Phase 2: Chunk processing for speed + DB write
        foreach (array_chunk($rows, 500) as $chunk) {
            $chunkResponseNew = [];

            foreach ($chunk as $key => $row) {
                $row = $this->sanitizeProductRow($row);

                if (!isset($row['Product_Name'], $row['Product_UOM'], $row['Inventory_Type'])) {
                    continue; // Skip invalid row
                }

                $row['Product_Name'] = Str::of($row['Product_Name'])->squish();

                $result = $this->validateProductRow(
                    $row,
                    $key,
                    $finalProductData,
                    $uomList['dataArr'],
                    $uomList['vls'],
                    $inventoryTypes['nms'],
                    $inventoryTypes['vls'],
                    $prod_name_arr
                );

                $response_data[] = $result['response'];
                $chunkResponseNew[] = $result['response_new'];
                $prod_name_arr = $result['prod_name_arr'];
            }

            if (!empty($chunkResponseNew)) {
                DB::table('temp_inventory_mgt')->insert($chunkResponseNew);
            }
        }

        return response()->json([
            'status' => 1,
            'message' => 'CSV file imported successfully',
            'uom_list' => $uomList['list'],
            'invt_type_list' => $inventoryTypes['list'],
            'data' =>  array_values($response_data),
        ]);
    }

    private function getUomMappings()
    {
        $uomList = Uom::where('status', 1)->get();
        $list = $vls = $dataArr = $vlsById = [];

        foreach ($uomList as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->uom_name];
            $vls[strtolower($row->uom_name)] = $row->uom_name;
            $dataArr[strtolower($row->uom_name)] = $row->id;
            $vlsById[$row->id] = $row->uom_name;
        }

        return compact('list', 'vls', 'dataArr', 'vlsById');
    }

    private function getInventoryTypeMappings()
    {
        $inventoryTypes = InventoryType::where('status', 1)->get();
        $list = $vls = $nms = $vlsById = [];

        foreach ($inventoryTypes as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->name];
            $vls[strtolower($row->name)] = $row->id;
            $nms[$row->id] = $row->name;
            $vlsById[$row->id] = $row->name;
        }

        return compact('list', 'vls', 'nms', 'vlsById');
    }

    private function sanitizeProductRow($row)
    {
        $row['Product_Specification'] = mb_convert_encoding($row['Product_Specification'] ?? '', 'UTF-8', 'ISO-8859-1');
        $row['Product_Size'] = mb_convert_encoding($row['Product_Size'] ?? '', 'UTF-8', 'ISO-8859-1');
        $row['Brand'] = $this->remove_special_chars($row['Brand'] ?? '');
        $row['Our_Product_Name'] = $this->remove_special_chars($row['Our_Product_Name'] ?? '');
        $row['Inventory_Grouping'] = $this->remove_special_chars($row['Inventory_Grouping'] ?? '');
        return $row;
    }
    private function validateProductRow(
        $vals,
        $key,
        $final_prod_data,
        $uom_data_arr,
        $uom_vls,
        $invt_type_nms,
        $invt_type_vls,
        $prod_name_arr
    ) {
        $error_code = '';
        $srno = $key + 1;

        $Product_Name = trim(preg_replace('/\s+/', ' ', $vals['Product_Name'] ?? ''));

        // $vals['Opening_Stock'] = is_numeric($vals['Opening_Stock'] ?? null) ? substr($vals['Opening_Stock'], 0, 20) : 0;
        // $vals['Stock_Price']   = is_numeric($vals['Stock_Price'] ?? null) ? substr($vals['Stock_Price'], 0, 20) : 0;
        $vals['Opening_Stock'] = (isset($vals['Opening_Stock']) && $vals['Opening_Stock'] !== '' && is_numeric($vals['Opening_Stock']))
            ? (float) $vals['Opening_Stock'] 
            : 0;

        $vals['Stock_Price'] = (isset($vals['Stock_Price']) && $vals['Stock_Price'] !== '' && is_numeric($vals['Stock_Price']))
            ? (float) $vals['Stock_Price']
            : 0;
        // Required field check
        $missing = [];
        if (empty($Product_Name)) $missing[] = 'Product Name';
        if (empty($vals['Product_UOM'])) $missing[] = 'UOM';
        if (!isset($vals['Inventory_Type']) || $vals['Inventory_Type'] === '') $missing[] = 'Inventory Type';

        if (!empty($missing)) {
            $action = implode(', ', $missing) . ' is Required';

            return [
                'response' => array_merge($vals, [
                    'srno' => $srno,
                    'action' => $action
                ]),
                'response_new' => [
                    'srno' => $srno,
                    'is_verify' => 2,
                    'user_id' => Auth::user()->id,
                    'action' => $action,
                    'data' => json_encode($vals),
                ],
                'prod_name_arr' => $prod_name_arr
            ];
        }

        if (strlen($Product_Name) < 3) {
            $error_code .= 'Product Name should be at least 3 characters, ';
        }

        // Enrich data if product exists
        if (!empty($final_prod_data[$Product_Name])) {
            $prod = $final_prod_data[$Product_Name];
            $vals['Product_Name'] = $prod->product_name ?? $Product_Name;
            $vals['Product_Specification'] = substr($vals['Product_Specification'] ?? '', 0, 2900);
            $vals['Product_Size'] = substr($vals['Product_Size'] ?? '', 0, 1450);
            $vals['Brand'] = substr($vals['Brand'] ?? '', 0, 100);
            $vals['Our_Product_Name'] = substr($vals['Our_Product_Name'] ?? '', 0, 100);
            $vals['Inventory_Grouping'] = substr($vals['Inventory_Grouping'] ?? '', 0, 100);
            $vals['Product_id'] = $prod->id ?? '';
            $vals['catid'] = $prod->category_id ?? '';
            $vals['divid'] = $prod->division_id ?? '';
            $vals['divname'] = '';
            $vals['catname'] = '';
        }

        $uom = strtolower($vals['Product_UOM']);
        $inventory = strtolower($vals['Inventory_Type'] ?? '');

        $vals['uom'] = $uom_data_arr[$uom] ?? 0;
        $vals['Inventory_Type'] = $invt_type_vls[$inventory] ?? 0;

        // UOM check
        if (!array_key_exists($uom, $uom_vls)) {
            $error_code .= 'Invalid UOM, ';
        }

        // Inventory Type check
        // if ($inventory && !array_key_exists($inventory, $invt_type_nms) && !array_key_exists($inventory, $invt_type_vls)) {
        //     $error_code .= 'Invalid Inventory Type, ';
        // }

        // Stock check
        $openingStock = (float) $vals['Opening_Stock'];
        $stockPrice   = (float) $vals['Stock_Price'];

        if (
            ($openingStock > 0 && $stockPrice > 0) || 
            ($openingStock == 0.0 && $stockPrice == 0.0)
        ) {
            // Do nothing
        } else {
            // if (is_null($openingStock)) {
            //     $error_code .= 'Opening stock is required, ';
            // }

            // if (is_null($stockPrice)) {
            //     $error_code .= 'Stock price is required, ';
            // }

            if (
                is_null($openingStock) || is_null($stockPrice)  
                // !is_null($openingStock) && !is_null($stockPrice) && 
                // !(($openingStock > 0 && $stockPrice > 0) || ($openingStock == 0.0 && $stockPrice == 0.0))
            ) {
                $error_code .= 'Invalid opening stock or stock price, ';
            }
        }


        $action = rtrim($error_code, ', ');
        $is_verify = $error_code ? 2 : 1;
        if (!$action) {
            $action = 'Product verified';
        }

        // Update unique key tracking
        $prod_name_arr[$Product_Name][$vals['Product_Size'] ?? ''][$vals['Product_Specification'] ?? ''] = $Product_Name;

        return [
            'response' => array_merge($vals, [
                'srno' => $srno,
                'action' => $action,
            ]),
            'response_new' => [
                'srno' => $srno,
                'is_verify' => $is_verify,
                'user_id' => Auth::user()->id,
                'action' => $action,
                'data' => json_encode($vals),
            ],
            'prod_name_arr' => $prod_name_arr
        ];
    }
    //-----------------------------------------end upload csv---------------------------------------------------------

    public function verify_product_for_supplier_new($product_name, $return_type = false)//pingki
    {
        if (empty($product_name)) {
            return $return_type ? null : 0;
        }

        $product = Product::whereRaw('LOWER(product_name) = ?', [strtolower(trim($product_name))])->first();

        return $return_type ? $product : ($product ? 1 : 0);
    }


    public function remove_special_chars($str){
        $clean_string = mb_convert_encoding($str, "UTF-8", "ISO-8859-1");
        return str_replace(array('<','>','"',"'",'`','~',"(",")", "/", "&", "…", ":", ";", "�"), '', $clean_string);
    }

    public function deleteRow(Request $request)
    {
        $srno       =   $request->srno;
        $user_id = Auth::user()->id;
        if (!$srno) {
            return response()->json(0);
        }

        $deleted = TempInventoryMgt::where('srno', $srno)
        ->where('user_id', $user_id)
        ->delete();
        return response()->json(['success' => (bool) $deleted]);


    }

    public function updateRowData(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $validated = $request->validate([
            'Product_Name'              => 'required|string|max:255',
            'Product_Specification'     => 'nullable|string|max:255',
            'Product_Size'              => 'nullable|string|max:100',
            'Opening_Stock'             => 'nullable|numeric',
            'Product_UOM'               => 'required|string|max:50',
            'Stock_Price'               => 'nullable|numeric',
            'Brand'                     => 'nullable|string|max:100',
            'Our_Product_Name'          => 'nullable|string|max:255',
            'Inventory_Grouping'        => 'nullable|string|max:255',
            'Inventory_Type'            => 'nullable|string|max:100',
            'Set_Min_Qty_for_Indent'    => 'nullable|numeric',
            'Product_id'                => 'nullable|numeric',
            'catid'                     => 'nullable|numeric',
            'Product_id'                => 'nullable|numeric',
            'divid'                     => 'nullable|numeric',
            'Product_id'                => 'nullable|numeric',
            'srno'                      => 'nullable|string',
            'action'                    => 'nullable|string',
            'is_verify'                 => 'required|numeric'
        ]);
        $srno = $request->input('srno');

        $data = [
            'product_name'              => $request->input('Product_Name'),
            'product_specification'     => $request->input('Product_Specification'),
            'product_size'              => $request->input('Product_Size'),
            'opening_stock'             => $request->input('Opening_Stock'),
            'product_uom'               => $request->input('Product_UOM'),
            'stock_price'               => $request->input('Stock_Price'),
            'brand'                     => $request->input('Brand'),
            'our_product_name'          => $request->input('Our_Product_Name'),
            'inventory_grouping'        => $request->input('Inventory_Grouping'),
            'inventory_type'            => $request->input('Inventory_Type'),
            'set_min_qty_for_indent'    => $request->input('Set_Min_Qty_for_Indent'),
            'product_id'                => $request->input('Product_id'),
            'category_id'               => $request->input('catid'),
            'division_id'               => $request->input('divid'),
            'action'                    => $request->input('action'),
            'is_verified'               => $request->input('is_verify'),
        ];

        $upd                                    =   array();
        $upd['data']                            =   json_encode($data);
        $upd['action']                          =   $request->input('action');
        $upd['srno']                            =   $request->input('srno');
        $upd['is_verify']                       =   $request->input('is_verify');
        // Run the update query
        $update = DB::table('temp_inventory_mgt')->where('srno', $srno)->update($upd);

        if ($update) {
            return response()->json(['status' => 'success', 'message' => 'Inventory row updated successfully']);
        } else {
            return response()->json(['status' => 'fail', 'message' => 'No matching record found or nothing changed']);
        }
    }

    public function checkBulkInventory(Request $request)
    {
        $buyer_id = Auth::user()->id;
        $query = TempInventoryMgt::select('temp_inventory_mgt.id')
        ->where('temp_inventory_mgt.user_id', $buyer_id)
        ->where('temp_inventory_mgt.is_verify', 1);
        $inventoryData = $query->get();

        if (!empty($inventoryData) && count($inventoryData) > 0) {
            return response()->json(1);
        } else {
            return response()->json(0);
        }
    }

    public function updateBulkProducts(Request $request)
    {
        $request = $this->trimAndReturnRequest($request);
        $branch_id      =   $request->input('buyer_branch');
        if (session('branch_id') != $request->input('buyer_branch')) {
                session(['branch_id' => $request->input('buyer_branch')]);
            }
        $company_id     =   Auth::user()->id;
        $buyer_id       =   Auth::user()->id;
        $query = TempInventoryMgt::select(
            'temp_inventory_mgt.id',
            'temp_inventory_mgt.is_verify',
            'temp_inventory_mgt.user_id',
            'temp_inventory_mgt.data'
        )
        ->where('temp_inventory_mgt.user_id', $buyer_id);
        $data = $query->get();
        $ins_data       =   array();
        $prokey         =   0;
        if(!empty($data)){
            $ins_data       =   array();
            $prokey         =   0;
            foreach($data as $rws){
                if($rws->is_verify==1){
                    $decoded    =   $rws->data;
                    if (is_array($decoded) || is_object($decoded)) {
                        $ins_data[$prokey] = (object) $decoded;
                        $ins_data[$prokey]->srno    =   $rws->srno;
                        $prokey++;
                    } elseif (is_string($decoded)) {
                        $ins_data[$prokey] = json_decode($decoded);
                        $ins_data[$prokey]->srno    =   $rws->srno;
                        $prokey++;
                    } else {

                    }
                }

            }
            $max_inv_id =   0;
            $inventory_unique_id = DB::table('inventories')
            ->where('buyer_parent_id', $company_id)
            ->where('buyer_branch_id', $branch_id)
            ->max('inventory_unique_id');

            if($inventory_unique_id){
                $max_inv_id =   ($inventory_unique_id);
            }
            $j                  =   1;
            $k                  =   0;
            $ns                 =   0;
            $tot                =   $data->count();
            $inserted           =   array();
            $prod_name_arr      =   array();
            $duplicate_array    =   array();
            $invProducts = DB::table('inventories')
            ->select('product_id', 'specification', 'size')
            ->where('buyer_parent_id', $company_id)
            ->where('buyer_branch_id', $branch_id)
            ->get();

            $prod_name_arr = [];

            if ($invProducts->isNotEmpty()) {
                foreach ($invProducts as $invProd) {
                    $spec   =   strtolower($invProd->specification ?? '');
                    $size   =   strtolower($invProd->size ?? '');
                    $prod_name_arr[$invProd->product_id][$spec][$size] = true;
                }
            }
            //echo "<pre>"; print_r($ins_data); die;
            foreach($ins_data as $i => $val){
                $csv_product_id             =   $val->Product_id ?? $val->product_id ?? '';
                $csv_product_specification  =   $val->Product_Specification ?? $val->product_specification ?? '';
                $csv_product_size           =   $val->Product_Size ?? $val->product_size ?? '';
                $csv_opening_stock          =   $val->Opening_Stock ?? $val->opening_stock ?? '';
                $csv_stock_price            =   $val->Stock_Price ?? $val->stock_price ?? '';
                $csv_product_uom            =   $val->uom ?? $val->product_uom ?? '';
                $csv_inventory_grouping     =   $val->Inventory_Grouping ?? $val->Inventory_Grouping ?? '';
                $csv_inventory_type         =   $val->Inventory_Type ?? $val->inventory_type ?? '';
                $set_min_qty_for_indent     =   $val->Set_Min_Qty_for_Indent ?? $val->set_min_qty_for_indent ?? '';
                $set_brand                  =   $val->Brand ?? $val->brand ?? '';

                if(!isset($prod_name_arr[$csv_product_id][strtolower($csv_product_specification)][strtolower($csv_product_size)])){

                    $inserted[$k]['inventory_unique_id']    =   ($max_inv_id) + $j;
                    $inserted[$k]['buyer_parent_id']        =   $buyer_id;
                    $inserted[$k]['buyer_branch_id']        =   $branch_id;
                    $inserted[$k]['product_id']             =   $csv_product_id;
                    $inserted[$k]['product_name']           =   $val->Product_Name ?? $val->product_name ?? '';
                    $inserted[$k]['buyer_product_name']     =   $val->Our_Product_Name ?? $val->our_product_name ?? '';
                    $inserted[$k]['specification']          =   substr($csv_product_specification,0,2900);
                    $inserted[$k]['size']                   =   substr($csv_product_size,0,1450);
                    $inserted[$k]['opening_stock']          =   isset($csv_opening_stock) && !empty($csv_opening_stock) && is_numeric($csv_opening_stock) ? $csv_opening_stock : '0';
                    $inserted[$k]['stock_price']            =   isset($csv_stock_price) && !empty($csv_stock_price)? $csv_stock_price : '0';
                    $inserted[$k]['uom_id']                 =   $csv_product_uom;
                    $inserted[$k]['inventory_grouping']     =   $csv_inventory_grouping;
                    $inserted[$k]['inventory_type_id']      =   isset($csv_inventory_type) && $csv_inventory_type !== '' && (int)$csv_inventory_type!=0 ? (int)$csv_inventory_type : NULL;
                    $inserted[$k]['indent_min_qty']         =   $set_min_qty_for_indent;
                    $inserted[$k]['product_brand']          =   $set_brand;
                    $inserted[$k]['created_by']             =   $buyer_id;
                    $inserted[$k]['updated_by']             =   $buyer_id;
                    $inserted[$k]['created_at']             =   date('Y-m-d H:i:s');
                    $inserted[$k]['updated_at']             =   date('Y-m-d H:i:s');
                    $prod_name_arr[$csv_product_id][strtolower($csv_product_specification)][strtolower($csv_product_size)]= $csv_product_size;
                    $j++;
                    $k++;
                }
                else{
                    if($ns<=500){
                        $duplicate_array[$val->srno]    =   $val->srno;
                    }
                    $ns++;
                }
            }
            $dataInserted   = false;
            foreach (array_chunk($inserted, 500) as $chunk) {
                $success = DB::table('inventories')->insert($chunk);
                if (!$success) {
                } else {
                    $dataInserted   =   true;
                }
            }
            if($dataInserted){
                return response()->json([
                    'status'        =>  1,
                    'save'          =>  $k,
                    'not_save'      =>  $ns,
                    'total'         =>  $tot,
                    'duplicate'     =>  $duplicate_array,
                    'message'       =>  $k.' Products uploded </br>'.$ns.' Products already exists in Inventory </br>'.$tot.' Total products upload attempted',
                ]); die;
            }else{
                return response()->json([
                    'status'        =>  2,
                    'save'          =>  $k,
                    'not_save'      =>  $ns,
                    'total'         =>  $tot,
                    'duplicate'     =>  $duplicate_array,
                    'message'       =>  $k.' Products uploded </br>'.$ns.' Products already exists in Inventory </br>'.$tot.' Total products upload attempted',
                ]); die;
            }
        }
        else{
            return response()->json([
                'status' => 1,
                'message' => 'No Data Found',
            ]); die;
        }
    }
}
