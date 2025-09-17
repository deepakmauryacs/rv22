<?php

namespace App\Http\Controllers\Buyer;

use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BranchDetail;
use App\Models\Category;
use App\Models\LiveVendorProduct;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\Uom;
use Carbon\Carbon;
use DB;
use Log;


class BulkRFQController extends Controller
{
    public function index(Request $request)
    {
        $branches = BranchDetail::getDistinctActiveBranchesByUser(Auth::user()->id);
        return view('buyer.rfq.bulk-rfq.index', compact('branches'));
    }
    public function uploadBulkExcel(Request $request)
    {
        Log::info('now coming in the upload');
       
        $file = $request->file('bulk_rfq_excel');
        
        if ($file->getClientOriginalExtension() !== 'xlsx') {
            return response()->json([
                'status' => 3,
                'message' => 'Only Excel file Allowed to upload Bulk RFQ'
            ]);
        }

        $productData = $this->excelToArray($file);
        
        if (empty($productData)) {
            return response()->json([
                'status' => 3,
                'all_product_data' => $productData,
                'message' => 'No Product Found to import Bulk RFQ....'               
            ]);
        }else{
            return response()->json([
                'status' => 2,                
                'all_product_data' => $productData,
                'message' => 'Uploaded Product Verified successfully'
            ]);
        }
    }


    private function excelToArray($file)
    {
        
        $data = Excel::toArray([], $file);
        $highestRow = count($data[0]);
        $allProductData = [];
        for ($row = 1; $row < $highestRow; $row++) {
            $productName = trim($data[0][$row][1]);
            $productName = preg_replace('/[^A-Za-z0-9\-,. ]/', '', $productName);
            
            if (!empty($productName)) {
                $brand = trim($data[0][$row][2]); 
                $remarks = trim($data[0][$row][3]);
                $specification = trim($data[0][$row][4]);
                $size = trim($data[0][$row][5]);
                $quantity = (int) $data[0][$row][6];
                $uom = trim($data[0][$row][7]);;

                $specification = substr($specification, 0, 255);

                $dataProduct = [
                    'product_name' => $productName,
                    'brand' => $brand,
                    'remarks' => $remarks,
                    'specification' => $specification,
                    'size' => $size,
                    'quantity' => $quantity,
                    'uom' => $uom,
                    'uom_id' => $this->getUOMIdByName($uom),
                    'uom_list' => $this->getUomlists()
                ];

                $allProductData[] = $dataProduct;
            }
        }

        //print_r($allProductData);
        // Take only first 100
        $allProductData = array_slice($allProductData, 0, 150);
        
        foreach ($allProductData as $key => $value) {
           
            if (strlen($value['product_name']) > 2) {
                $validPName = explode(',', $value['product_name']);
                
                if (count($validPName) > 1) {
                    $allProductData[$key]['product_name'] = trim($validPName[0]);
                    $extra = trim($validPName[1]);
                    $allProductData[$key]['specification'] .= !empty($extra) ? ', ' . $extra : '';
                }

               $allProductData[$key]['product_name'] = strtolower($allProductData[$key]['product_name']);

                //Log::info('lower products'.$allProductData[$key]['product_name']);
                //echo $allProductData[$key]['product_name']; die();
                // Replace with your actual product search logic
                $productDetail = Product::where('product_name', $allProductData[$key]['product_name'])->first();
                
                
                if ($productDetail && isset($productDetail->product_name) && $productDetail->status == 1) {
                    $allProductData[$key]['product_name'] = $productDetail->product_name;
                    $allProductData[$key]['status'] = 1;
                    $allProductData[$key]['message'] = "Product Verified";
                } else {
                    $allProductData[$key]['status'] = 2;
                    $allProductData[$key]['message'] = "Invalid Product";
                }
            } else {
                $allProductData[$key]['status'] = 2;
                $allProductData[$key]['message'] = "Invalid Product";
            }
        }
        
        
        return [
            'all_product_data' => $allProductData
        ];
    }

    private function getUomlists()
    {
        $uomList = Uom::where('status', 1)
                  ->orderBy('id', 'asc')
                  ->get();
            
        $list = [];

        foreach ($uomList as $i => $row) {
            $list[$i] = ['id' => $row->id, 'name' => $row->uom_name];            
        }

        return compact('list');
    }

    private function getUOMIdByName($uom){
        $uomRecord = Uom::where('status', 1)
                    ->where('uom_name', $uom)
                    ->select('id')
                    ->first();
        return $uomRecord ? $uomRecord->id : null;
    }

    public function getAllUOMLists(){
        $allUOMLists = $this->getUomlists();
        return response()->json([
                'status' => 2,
                'all_uom_list' => $allUOMLists
            ]);
    }
    public function validateProductName(Request $request){
        //Log::info('product name'.$request->product_name);
        $product_name = $request->product_name;
        $source = $request->source;
        
        if(empty($product_name)){
            return response()->json([
                'status' => false,
                'message' => 'Please enter product name'
            ]);
        }
        $page = $request->page;

        $draft_products = array();

        $product_name = LiveVendorProduct::cleanString($product_name);

        $search_arr = array(
            'product_name' => $product_name,
            'per_page' => 20,
            'page' => $page,
            'draft_products' => $draft_products,
        )
        ;

        $is_suggesation = $request->is_suggesation;
        
        $products = LiveVendorProduct::getSearchedProduct($search_arr);
        
        return response()->json([
            'status' => true,
            'message' => 'Product found',
            'is_products' => count($products)>0 ? true : false,
            'is_suggesation' => $is_suggesation,
            'products' => $products,
        ]);
    }

    function bulkDraftRFQ(Request $request) {

        // dd($request->all());
        $prn_no = $request->prnNumber;
        $buyer_branch = $request->branch_id;
        if (empty($buyer_branch)) {
            $buyer_branch = 0;
        }
        $last_date_of_reponse = $request->last_date_to_response;
        $last_resp_dt = null;
        if (!empty($last_date_of_reponse)) {
            $last_resp_dt = isset($last_date_of_reponse) && !empty($last_date_of_reponse) ? changeCustomDateFormate($last_date_of_reponse) : '';
        }
        
        $rfq_draft_id = 'D'.time().rand(1000, 9999);
        $userdata = Auth::user()->id;
        $buyer_user_id  = Auth::user()->id;
        $buyer_id = getParentUserId();
        
        

        DB::beginTransaction();
        try {
            $rfq = new Rfq();
            $rfq->rfq_id = $rfq_draft_id;
            $rfq->buyer_id = $buyer_id;
            $rfq->buyer_user_id = $buyer_user_id;
            $rfq->buyer_branch = $buyer_branch;
            $rfq->prn_no = $prn_no;
            $rfq->last_response_date = $last_resp_dt;
            $rfq->record_type = 1;
            $rfq->is_bulk_rfq = 1;
            $rfq->buyer_rfq_status = 1;
            $rfq->save();
            $products = $request->input('product_name');
            
            $i = 0;
            foreach ($products as $val) {
                $productName = $val ?? null;

                if (!empty($productName)) {
                    $product = Product::where('status', 1)
                        ->where('product_name', $productName)
                        ->first();
                    // Ensure the product exists on RFQs
                    if ($product && $product->id > 0) {                       
                        $alreadyExists = RfqProduct::where('rfq_id', $rfq->rfq_id)
                            ->where('product_id', $product->id)
                            ->exists();

                        if (!$alreadyExists) {
                            $rfqProduct = new RfqProduct();
                            $rfqProduct->rfq_id = $rfq->rfq_id;
                            $rfqProduct->product_id = $product->id;
                            $rfqProduct->brand = $request->input('brand.' . $i) ?? null;
                            $rfqProduct->remarks = $request->input('remarks.' . $i) ?? null;
                            $rfqProduct->product_order = 1;
                            $rfqProduct->save();
                        }
                        $variant_grp_id = now()->timestamp . mt_rand(10000, 99999);
                       
                        $rfqProductVariant = new RfqProductVariant();
                        $rfqProductVariant->rfq_id = $rfq->rfq_id;
                        $rfqProductVariant->product_id = $product->id;
                        $rfqProductVariant->specification = $request->input('specification.' . $i) ?? null;
                        $rfqProductVariant->specification = $request->input('size.' . $i) ?? null;
                        $rfqProductVariant->uom = $request->input('uom.' . $i) ?? null;
                        $rfqProductVariant->quantity = $request->input('quantity.' . $i) ?? null;
                        $rfqProductVariant->variant_order = 1;
                        $rfqProductVariant->variant_grp_id = $variant_grp_id;
                        $rfqProductVariant->save();
                    }
                }
                $i++;
            }         
            DB::commit();            
            return response()->json([
                'status' => 1,
                'message' => 'RFQ Draft created',
                'url' => route('buyer.rfq.compose-draft-rfq', ['draft_id' =>$rfq->rfq_id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to create RFQ Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }
}
