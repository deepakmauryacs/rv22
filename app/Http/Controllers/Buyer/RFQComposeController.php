<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendor;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class RFQComposeController extends Controller
{
    public $rfq_attachment_dir = 'rfq-attachment';

    function index($draft_id) {
        $company_id = getParentUserId();

        $draft_rfq = Rfq::where('rfq_id', $draft_id)->where('buyer_id', $company_id)->whereIn('record_type', [1, 3])->first();
        if(empty($draft_rfq)){
            session()->flash('error', "Draft RFQ not found");
            return redirect()->to(route('buyer.dashboard'));
        }

        $buyer_branch = DB::table('branch_details')
            ->select('id', 'branch_id', 'name')
            ->where("user_id", $company_id)
            ->where('user_type', 1)
            ->where('record_type', 1)
            ->where('status', 1)
            ->get();
        $uoms = DB::table('uoms')
                        ->select("id", "uom_name")
                        ->where("status", 1)
                        ->orderBy("id", "ASC")
                        ->pluck("uom_name", "id")->toArray();
        $dealer_types = DB::table("dealer_types")
                            ->select("id", "dealer_type")
                            ->where("status", 1)
                            ->orderBy("id", "ASC")
                            ->pluck("dealer_type", "id")->toArray();

        return view('buyer.rfq.compose', compact('draft_rfq', 'uoms', 'buyer_branch', 'dealer_types'));
    }

    function getDraftProduct(Request $request) {
        $draft_id = $request->draft_id;
        if(empty($draft_id)){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
        $company_id = getParentUserId();
        // $current_user_id = Auth::user()->id;

        $draft_rfq = Rfq::where('rfq_id', $draft_id)->where('buyer_id', $company_id)->whereIn('record_type', [1, 3])->first();

        if(empty($draft_rfq)){
            return response()->json([
                'status' => false,
                'message' => 'Draft RFQ not found'
            ]);
        }

        // $draft_rfq = Rfq::with([
        //                 'rfqProducts',
        //                 'rfqProducts.masterProduct:id,division_id,category_id,product_name',
        //                 'rfqProducts.masterProduct.division:id,division_name',
        //                 'rfqProducts.masterProduct.category:id,category_name',
        //                 'rfqProducts.productVariants' => function ($q) use ($draft_id) {
        //                     $q->where('rfq_id', $draft_id);
        //                 },
        //                 'rfqVendors:id,rfq_id,vendor_user_id,product_id',
        //                 'rfqVendors.productVendors' => function ($q) {
        //                     $q->select('id', 'vendor_id', 'product_id')
        //                         ->where('vendor_status', '1')
        //                         ->where('edit_status', 0)
        //                         ->where('approval_status', 1)
        //                         ->whereHas('product', function ($q2) {
        //                             $q2->where('status', 1);
        //                         })
        //                         ->whereHas('vendor_profile', function ($q2) {
        //                             $q2->whereNotNull('vendor_code')
        //                                 ->whereHas('user', function ($q3) {
        //                                     $q3->where('status', 1)
        //                                         ->where('is_verified', 1)
        //                                         ->where('user_type', 2);
        //                                 });
        //                         });
        //                 },
        //                 'rfqProducts.productVendors.vendor_profile:id,user_id,legal_name,state,country',
        //                 'rfqProducts.productVendors.vendor_profile.vendor_state:id,name,country_id',
        //                 'rfqProducts.productVendors.vendor_profile.vendor_country:id,name',
        //             ])
        //             ->where('rfq_id', $draft_id)
        //             ->where('buyer_id', $company_id)
        //             ->where('record_type', 1)
        //             ->first();
        // $start = microtime(true);

        $draft_rfq = Rfq::withWhereHas('rfqProducts.productVendors', function ($q) {
                        $q->select('id', 'vendor_id', 'product_id')  // include FK 'vendor_id'
                        ->where('vendor_status', 1)
                        ->where('edit_status', 0)
                        ->where('approval_status', 1)
                        ->whereHas('product', function ($q2) {
                            $q2->where('status', 1);
                        })
                        ->whereHas('vendor_profile', function ($q2) {
                            $q2->whereNotNull('vendor_code')
                                ->whereHas('user', function ($q3) {
                                    $q3->where('status', 1)
                                        ->where('is_verified', 1)
                                        ->where('user_type', 2);
                                });
                        });
                    })
                    ->with([
                        'rfqProducts',
                        'rfqProducts.masterProduct:id,division_id,category_id,product_name',
                        'rfqProducts.masterProduct.division:id,division_name',
                        'rfqProducts.masterProduct.category:id,category_name',
                        'rfqProducts.productVariants' => function ($q) use ($draft_id) {
                            $q->where('rfq_id', $draft_id);
                        },
                        'rfqVendors:id,rfq_id,vendor_user_id,product_id',
                        'rfqProducts.productVendors' => function ($q) {
                            $q->select('id', 'vendor_id', 'product_id', 'vendor_status', 'edit_status', 'approval_status')
                                ->where('vendor_status', 1)
                                ->where('edit_status', 0)
                                ->where('approval_status', 1);
                        },
                        'rfqProducts.productVendors.vendor_profile:id,user_id,legal_name,state,country',
                        'rfqProducts.productVendors.vendor_profile.vendor_state:id,name,country_id',
                        'rfqProducts.productVendors.vendor_profile.vendor_country:id,name',
                    ])
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->whereIn('record_type', [1, 3])
                    ->first();

        // $end = microtime(true);
        // $execution_time = $end - $start;
        $rfq_vendors = $this->extractRFQVendors($draft_rfq->rfqVendors);

        // print_r($draft_rfq->rfqProducts[0]['productVendors'][0]->vendor_profile['legal_name']);
        // print_r($rfq_vendors);
        // echo "<pre>";
        // print_r($draft_rfq);
        // die;


        $uoms = DB::table('uoms')
                        ->select("id", "uom_name")
                        ->where("status", 1)
                        ->orderBy("id", "ASC")
                        ->pluck("uom_name", "id")->toArray();

        $product_html = view('buyer.rfq.rfq-product-item', compact('draft_rfq', 'uoms', 'rfq_vendors'))->render();

        $vendor_locations = $this->extractVendorsLocation($draft_rfq);

        return response()->json([
            'status' => true,
            'message' => 'Draft RFQ found',
            'products' => $product_html,
            'all_states' => $vendor_locations['states'],
            'all_country' => $vendor_locations['countries'],
            // 'execution_time' => 'Execution Time: ' . $execution_time . ' seconds',
        ]);
    }

    private function extractRFQVendors($rfqVendors){
        return collect($rfqVendors)
            ->filter() // removes nulls or falsy entries
            ->pluck('vendor_user_id')
            ->values()
            ->all();
    }
    private function extractVendorsLocation($draft_rfq){
        $states = collect();
        $countries = collect();
        foreach ($draft_rfq->rfqProducts as $product) {
            foreach ($product->productVendors as $vendor) {
                $profile = $vendor->vendor_profile;

                if ($profile?->vendor_country && $profile->vendor_country->id==101) {
                    $states->push($profile->vendor_state);
                }else{
                    $countries->push($profile->vendor_country);
                }
            }
        }

        // Remove duplicates by ID
        $uniqueStates = $states->unique('id')->sortBy('name')->values();
        $uniqueCountries = $countries->unique('id')->sortBy('name')->values();

        unset($countries);
        unset($states);

        return array('states'=> $uniqueStates, 'countries'=> $uniqueCountries);
    }
    private function isDraftExists($draft_id, $company_id){
        return DB::table('rfqs')
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->whereIn('record_type', [1, 3])
                    ->first();
    }

    function updateProduct(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $current_user_id = Auth::user()->id;
        $master_product_id = $request->master_product_id;
        // $vendor_id = $request->vendor_id;
        $brand = $request->brand;
        $remarks = $request->remarks;
        $edit_id = $request->edit_id;
        $variant_grp_id = $request->variant_grp_id;
        $variant_order = $request->variant_order;
        $specification = $request->specification;
        $size = $request->size;
        $quantity = $request->quantity;
        $uom = $request->uom;
        $old_attachment = $request->old_attachment;
        $delete_attachment = $request->delete_attachment;

        // echo "<pre>";
        // print_r($_FILES);
        // die;
        DB::beginTransaction();

        try {

            RfqProduct::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->update(["brand"=> $brand, "remarks"=> $remarks]);
            $is_file_uploaded = '';
            $is_file_deleted = '';
            $attachments = $request->file('attachment');
            if(!empty($edit_id) && count($edit_id)>0){
                $file_prefix = 'B' . $current_user_id. '-R';

                foreach ($edit_id as $key => $value) {
                    $is_new_variant = true;
                    $is_variant_exists = RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id[$key])
                        ->first();
                    if(!empty($is_variant_exists)){
                        $is_new_variant = false;
                    }

                    $rfq_file_name = null;
                    if (is_array($attachments) && isset($attachments[$key])) {
                        $res = uploadMultipleFile($request, 'attachment', $this->rfq_attachment_dir, $key, $file_prefix);
                        if($res['status']){
                            if($is_new_variant==false && !empty($is_variant_exists->attachment)){
                                removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$is_variant_exists->attachment));
                            }
                            $rfq_file_name = $res['file_name'];
                            $is_file_uploaded = $res['file_name'];
                        }else{
                            throw new \Exception($res['file_name']);
                        }
                    }else if(!empty($request->old_attachment[$key])){
                        $rfq_file_name = $request->old_attachment[$key];
                    }

                    if(!empty($delete_attachment[$key]) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$delete_attachment[$key]))){
                        removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$delete_attachment[$key]));
                        $is_file_deleted = $delete_attachment[$key];
                    }

                    if($is_new_variant==true){
                        $rfqProductVariant = new RfqProductVariant();
                        $rfqProductVariant->rfq_id = $draft_id;
                        $rfqProductVariant->product_id = $master_product_id;
                        $rfqProductVariant->variant_order = $variant_order[$key];
                        $rfqProductVariant->variant_grp_id = $variant_grp_id[$key];
                        $rfqProductVariant->specification = $specification[$key];
                        $rfqProductVariant->size = $size[$key];
                        $rfqProductVariant->quantity = $quantity[$key] ?? null;
                        $rfqProductVariant->uom = $uom[$key];
                        $rfqProductVariant->attachment = $rfq_file_name;
                        $rfqProductVariant->save();
                    }else{
                        RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id[$key])
                        ->update(
                            [
                                "specification"=> $specification[$key],
                                "size"=> $size[$key],
                                "quantity"=> $quantity[$key] ?? null,
                                "uom"=> $uom[$key],
                                "attachment"=> $rfq_file_name,
                                "variant_order"=> $variant_order[$key]
                            ]
                        );
                    }
                }
            }

            // $existing_vendors = DB::table("rfq_vendors")->where("rfq_id", $draft_id)->pluck('vendor_user_id');

            // // 1. Get new vendors (in $vendor_id but not in $existing_vendors)
            // $new_vendors = array_values(array_diff($vendor_id, $existing_vendors->toArray()));

            // // 2. Get removed vendors (in $existing_vendors but not in $vendor_id)
            // $removed_vendors = array_values(array_diff($existing_vendors->toArray(), $vendor_id));

            // // insert only new vendor
            // if(count($new_vendors)>0){
            //     foreach ($new_vendors as $vendor_user_id) {
            //         $rfqVendor = new RfqVendor();
            //         $rfqVendor->rfq_id = $draft_id;
            //         $rfqVendor->vendor_user_id = $vendor_user_id;
            //         $rfqVendor->vendor_status = 1;
            //         $rfqVendor->save();
            //     }
            // }

            // // delete old vendor
            // if(count($removed_vendors)>0){
            //     RfqVendor::where("rfq_id", $draft_id)->whereIn("vendor_user_id", $removed_vendors)->delete();
            // }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ updated successfully',
                'is_file_deleted'=> $is_file_deleted,
                'is_file_uploaded'=> $is_file_uploaded,
                'file_url'=> $is_file_uploaded !='' ? url('public/uploads/rfq-attachment') : ''
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update draft RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function updateDraftRFQ(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }
        $prn_no = $request->prn_no;
        $buyer_branch = $request->buyer_branch;
        $last_response_date = $request->last_response_date;
        $buyer_price_basis = $request->buyer_price_basis;
        $buyer_pay_term = $request->buyer_pay_term;
        $buyer_delivery_period = $request->buyer_delivery_period;
        $warranty_gurarantee = $request->warranty_gurarantee;
        $vendor_id = $request->vendor_id;
        $scheduled_date = $request->rfq_schedule_date;

        if(!empty($last_response_date)){
            $last_response_date = Carbon::createFromFormat('d/m/Y', $last_response_date)->format('Y-m-d');
        }
        if(!empty($scheduled_date)){
            $scheduled_date = Carbon::createFromFormat('d/m/Y', $scheduled_date)->format('Y-m-d');
        }

        // --------------------------------updating vendors START------------------------------------------
        $existing_vendors = DB::table("rfq_vendors")->select('vendor_user_id', 'product_id')->where("rfq_id", $draft_id)->get()->toArray();
        $existing_vendor_product = [];
        foreach($existing_vendors as $key => $val){
            $existing_vendor_product[$val->vendor_user_id][] = $val->product_id;
        }

        // // 1. Get new vendors (in $vendor_id but not in $existing_vendors)
        // $new_vendors = array_values(array_diff($vendor_id, $existing_vendors->toArray()));

        // // 2. Get removed vendors (in $existing_vendors but not in $vendor_id)
        // $removed_vendors = array_values(array_diff($existing_vendors->toArray(), $vendor_id));

        $rfq_products = DB::table('rfq_products')->select('product_id')->where("rfq_id", $draft_id)->pluck('product_id')->toArray();

        $product_vendors = DB::table("vendor_products")
            ->select('vendor_id', 'product_id')
            ->join('users', 'vendor_products.vendor_id', '=', 'users.id')
            ->join('vendors', 'vendors.user_id', '=', 'users.id')
            ->whereIn("vendor_products.vendor_id", $vendor_id)
            ->whereIn("vendor_products.product_id", $rfq_products)
            ->where('vendor_products.vendor_status', 1)
            ->where('vendor_products.edit_status', 0)
            ->where('vendor_products.approval_status', 1)
            ->whereNotNull('vendors.vendor_code')
            ->where('users.status', 1)
            ->where('users.is_verified', 1)
            ->where('users.user_type', 2)
            ->get()
            ->toArray();
        $product_vendors_arr = [];
        foreach($product_vendors as $key => $val){
            $product_vendors_arr[$val->vendor_id][] = $val->product_id;
        }

        $added_vendor_products = [];
        $removed_vendor_products = [];

        // foreach($existing_vendor_product as $exting_vendor_id => $product_ids){
        //     if(!isset($product_vendors_arr[$exting_vendor_id])){
        //         $added_vendor_products[$exting_vendor_id] = [];
        //     }
        //     foreach($product_ids as $product_id){
        //         if(!isset($product_vendors_arr[$exting_vendor_id][$product_id])){
        //             $added_vendor_products[$exting_vendor_id][] = $product_id;
        //         }
        //     }
        // }

        // foreach($product_vendors_arr as $vendor_id => $product_ids){
        //     if(!isset($existing_vendor_product[$vendor_id])){
        //         $removed_vendor_products[$vendor_id] = [];
        //     }
        //     foreach($product_ids as $product_id){
        //         if(!isset($existing_vendor_product[$vendor_id][$product_id])){
        //             $removed_vendor_products[$vendor_id][] = $product_id;
        //         }
        //     }
        // }
        // Find removed vendor products and vendors
        foreach ($existing_vendor_product as $existing_vendor_id => $product_ids) {
            if (!isset($product_vendors_arr[$existing_vendor_id])) {
                // Vendor removed completely, so all products are removed
                $removed_vendor_products[$existing_vendor_id] = $product_ids;
            } else {
                // Check for removed products for this vendor
                foreach ($product_ids as $product_id) {
                    if (!in_array($product_id, $product_vendors_arr[$existing_vendor_id])) {
                        $removed_vendor_products[$existing_vendor_id][] = $product_id;
                    }
                }
            }
        }

        // Find added vendor products and vendors
        foreach ($product_vendors_arr as $vendor_id => $product_ids) {
            if (!isset($existing_vendor_product[$vendor_id])) {
                // New vendor added, so all products are added
                $added_vendor_products[$vendor_id] = $product_ids;
            } else {
                // Check for added products for this vendor
                foreach ($product_ids as $product_id) {
                    if (!in_array($product_id, $existing_vendor_product[$vendor_id])) {
                        $added_vendor_products[$vendor_id][] = $product_id;
                    }
                }
            }
        }
        unset($product_vendors_arr);
        unset($existing_vendor_product);

        // echo "<pre>";
        // echo "<br>existing_vendor_product: "; print_r($existing_vendor_product); echo "<br>";
        // echo "<br>product_vendors_arr: "; print_r($product_vendors_arr); echo "<br>";
        // echo "<br>added_vendor_products: "; print_r($added_vendor_products); echo "<br>";
        // echo "<br>removed_vendor_products: "; print_r($removed_vendor_products); echo "<br>";
        // die;

        // insert only new vendor
        // if(count($new_vendors)>0){
        //     foreach ($new_vendors as $vendor_user_id) {
        //         $rfqVendor = new RfqVendor();
        //         $rfqVendor->rfq_id = $draft_id;
        //         $rfqVendor->vendor_user_id = $vendor_user_id;
        //         // $rfqVendor->product_id = $product_id;
        //         $rfqVendor->vendor_status = 1;
        //         $rfqVendor->save();
        //     }
        // }

        // delete old vendor
        // if(count($removed_vendors)>0){
        //     RfqVendor::where("rfq_id", $draft_id)->whereIn("vendor_user_id", $removed_vendors)->delete();
        // }
        // --------------------------------updating vendors END------------------------------------------

        DB::beginTransaction();

        try {

            Rfq::where("rfq_id", $draft_id)
                ->update(
                    [
                        "prn_no"=> $prn_no,
                        "buyer_branch"=> $buyer_branch,
                        "last_response_date"=> $last_response_date,
                        "buyer_price_basis"=> $buyer_price_basis,
                        "buyer_pay_term"=> $buyer_pay_term,
                        "buyer_delivery_period"=> $buyer_delivery_period,
                        "warranty_gurarantee"=> $warranty_gurarantee,
                        "scheduled_date"=> $scheduled_date,
                    ]
                );
            // insert only new vendor
            if(count($added_vendor_products)>0){
                $vendor_inserted = [];
                foreach ($added_vendor_products as $vendor_user_id => $product_ids) {
                    foreach ($product_ids as $product_id) {
                        $vendor_inserted[] = [
                            'rfq_id' => $draft_id,
                            'vendor_user_id' => $vendor_user_id,
                            'product_id' => $product_id,
                            'vendor_status' => 1
                        ];
                    }
                }
                if(count($vendor_inserted)>0){
                    RfqVendor::insert($vendor_inserted);
                }
            }

            // delete old vendor
            if(count($removed_vendor_products)>0){
                foreach ($removed_vendor_products as $vendor_user_id => $product_ids) {
                    RfqVendor::where("rfq_id", $draft_id)->whereIn("product_id", $product_ids)->where("vendor_user_id", $vendor_user_id)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update draft RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteProduct(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $master_product_id = $request->master_product_id;

        $distinct_vendor_before = DB::table('rfq_vendors')
            ->where('rfq_id', $draft_id)
            ->distinct('vendor_user_id')
            ->count('vendor_user_id');
        // echo "<pre>";
        // print_r($distinct_vendor_before);
        // die;

        $total_product_count = RfqProduct::where("rfq_id", $draft_id)->count();

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->where("product_id", $master_product_id)
                                ->select("attachment")
                                ->get();

        DB::beginTransaction();

        try {

            RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->delete();

            RfqProduct::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->delete();

            RfqVendor::where("rfq_id", $draft_id)->where("product_id", $master_product_id)->delete();

            if($total_product_count==1){
                Rfq::where("rfq_id", $draft_id)->delete();
            }
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            $distinct_vendor_after = DB::table('rfq_vendors')
                    ->where('rfq_id', $draft_id)
                    ->distinct('vendor_user_id')
                    ->count('vendor_user_id');
            $is_vendor_updated = false;
            $updated_vendor = [];
            if($distinct_vendor_before > $distinct_vendor_after){
                // refresh vendor list
                $updated_vendor = $this->refreshVendorList($draft_id);
                $is_vendor_updated = true;
            }

            return response()->json([
                'status' => true,
                'is_vendor_updated' => $is_vendor_updated,
                'updated_vendor' => $updated_vendor,
                'message' => 'Draft RFQ product deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete draft RFQ product. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

    private function refreshVendorList($draft_id){
        $company_id = getParentUserId();
        $draft_rfq = Rfq::with([
                        'rfqProducts',
                        // 'rfqProducts.masterProduct:id,division_id,category_id,product_name',
                        // 'rfqProducts.masterProduct.division:id,division_name',
                        // 'rfqProducts.masterProduct.category:id,category_name',
                        'rfqProducts.productVariants' => function ($q) use ($draft_id) {
                            $q->where('rfq_id', $draft_id);
                        },
                        'rfqVendors:id,rfq_id,vendor_user_id,product_id',
                        'rfqVendors.productVendors' => function ($q) {
                            $q->select('id', 'vendor_id', 'product_id')
                                ->where('vendor_status', 1)
                                ->where('edit_status', 0)
                                ->where('approval_status', 1);
                            $q->whereHas('product', function ($q2) {
                                $q2->where('status', 1);
                            });

                            $q->whereHas('vendor_profile', function ($q2) {
                                $q2->whereNotNull('vendor_code')
                                    ->whereHas('user', function ($q3) {
                                        $q3->where('status', 1)
                                            ->where('is_verified', 1)
                                            ->where('user_type', 2);
                                    });
                            });
                        },
                        'rfqProducts.productVendors.vendor_profile:id,user_id,legal_name,state,country',
                        'rfqProducts.productVendors.vendor_profile.vendor_state:id,name,country_id',
                        'rfqProducts.productVendors.vendor_profile.vendor_country:id,name',
                    ])
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->whereIn('record_type', [1, 3])
                    ->first();
        $rfq_vendors = $this->extractRFQVendors($draft_rfq->rfqVendors);

        $vednor_html = view('buyer.rfq.rfq-vendors', compact('draft_rfq', 'rfq_vendors'))->render();

        $vendor_locations = $this->extractVendorsLocation($draft_rfq);

        return [
            'vednor_html' => $vednor_html,
            'all_states' => $vendor_locations['states'],
            'all_country' => $vendor_locations['countries']
        ];
    }
    function deleteProductVariant(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $master_product_id = $request->master_product_id;
        $variant_grp_id = $request->variant_grp_id;


        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->where("product_id", $master_product_id)
                                ->where("variant_grp_id", $variant_grp_id)
                                ->select("attachment")
                                ->get();
        // echo "<pre>";
        // print_r($rfqProductVariant);
        // die;

        DB::beginTransaction();
        try {

            RfqProductVariant::where("rfq_id", $draft_id)
                        ->where("product_id", $master_product_id)
                        ->where("variant_grp_id", $variant_grp_id)
                        ->delete();

            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft RFQ product variant deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete draft RFQ product variant. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteDraftRFQ(Request $request) {

        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        if($is_draft_exists->record_type == 3){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Edited RFQ cannot be deleted.',
            ]);
        }

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->select("attachment")
                                ->get();
        // 

        DB::beginTransaction();

        try {

            RfqProductVariant::where("rfq_id", $draft_id)->delete();
            RfqProduct::where("rfq_id", $draft_id)->delete();
            RfqVendor::where("rfq_id", $draft_id)->delete();
            Rfq::where("rfq_id", $draft_id)->delete();
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Draft deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }
    function deleteEditedRFQ(Request $request) {
        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();
        $is_draft_exists = $this->isDraftExists($draft_id, $company_id);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        if($is_draft_exists->record_type == 1){
            return response()->json([
                'status' => false,
                'message' => 'Draft RFQ cannot be deleted.',
            ]);
        }

        $rfqProductVariant = DB::table('rfq_product_variants')
                                ->where("rfq_id", $draft_id)
                                ->select("attachment")
                                ->get();
        // 

        DB::beginTransaction();

        try {

            RfqProductVariant::where("rfq_id", $draft_id)->delete();
            RfqProduct::where("rfq_id", $draft_id)->delete();
            RfqVendor::where("rfq_id", $draft_id)->delete();
            Rfq::where("rfq_id", $draft_id)->delete();
            DB::commit();

            foreach ($rfqProductVariant as $key => $value) {
                if(!empty($value->attachment) && is_file(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment))){
                    removeFile(public_path('uploads/'.$this->rfq_attachment_dir.'/'.$value->attachment));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Edited RFQ deleted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Draft. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

    public function searchVendors(Request $request)
    {
        $q = trim($request->post('q'));
        $rfq_id = trim($request->post('rfq_id'));
        $states = $request->post('states');
        $country = $request->post('country');
        $page = (int) $request->post('page', 1);

        if (mb_strlen($q) < 4) {
            return response()->json(['status' => true, 'data' => [], 'has_more' => false]);
        }

        // get vendors from vendor product tbl
        // $rfq_vendors = DB::table('rfq_vendors')->select('vendor_user_id')->where('rfq_id', $rfq_id)->distinct()->pluck('vendor_user_id')->toArray();
        $rfq_products = DB::table('rfq_products')->select('product_id')->where('rfq_id', $rfq_id)->distinct()->pluck('product_id')->toArray();

        $product_vendors = DB::table('vendor_products')
            ->select('vendor_id')
            ->distinct()
            ->whereIn('product_id', $rfq_products)
            ->where('vendor_status', 1)
            ->where('edit_status', 0)
            ->where('approval_status', 1)
            ->pluck('vendor_id')->toArray();


        $limit = 10;
        $offset = ($page - 1) * $limit;

        // Split search string into words
        $words = preg_split('/\s+/', $q);

        $resultQuery = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'users.mobile',
                'users.country_code',
                'vendors.legal_name',
                'vendors.vendor_code',
                'vendors.country as country_id',
                'countries.name as country_name',
                'states.name as state_name',
            )
            ->join('vendors', 'vendors.user_id', '=', 'users.id')
            ->join('countries', 'vendors.country', '=', 'countries.id')
            ->join('states', 'vendors.state', '=', 'states.id')
            ->where('users.user_type', '2') // 2 = Vendor
            ->where('users.is_verified', 1)
            ->where('users.status', '1');   // Active users

        if (!empty($states) && !empty($country)) {
            $resultQuery->where(function ($query) use ($states, $country) {
                $query->whereIn('vendors.state', $states)
                    ->orWhereIn('vendors.country', $country);
            });
        } else {
            if(!empty($states)){
                $resultQuery->whereIn('vendors.state', $states);
            }

            if(!empty($country)){
                $resultQuery->whereIn('vendors.country', $country);
            }
        }
        if(!empty($product_vendors)){
            $resultQuery->whereNotIn('vendors.user_id', $product_vendors);
        }
        // echo "<pre>";
        // print_r($product_vendors);
        // die;
        // Each word must be in legal_name (AND logic)
        foreach ($words as $word) {
            $resultQuery->where('vendors.legal_name', 'LIKE', "%{$word}%");
        }

        $result = $resultQuery
            ->orderBy('vendors.legal_name')
            ->offset($offset)
            ->limit($limit + 1) // +1 to check for more pages
            ->get();

        $has_more = $result->count() > $limit;

        $data = $result->slice(0, $limit)->map(function ($user) {
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'mobile'       => $user->mobile,
                'country_code' => $user->country_code ?? '',
                'legal_name'   => $user->legal_name ?? '',
                'vendor_code'  => $user->vendor_code ?? '',
                'country_id'   => $user->country_id ?? '',
                'country_name' => $user->country_name ?? '',
                'state_name'   => $user->state_name ?? '',
            ];
        })->values();

        return response()->json([
            'status'   => true,
            'data'     => $data,
            'has_more' => $has_more
        ]);
    }

    function addVendorToRFQ(Request $request)
    {
        $rfq_id = $request->post('rfq_id');
        $vendor_array = $request->post('vendor_array');
        $first_product_id = $request->post('first_product_id');

        if(!is_array($vendor_array) || count($vendor_array)<=0){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }

        $rfq = DB::table('rfqs')->where('rfq_id', $rfq_id)->whereIn('record_type', [1, 3])->first();
        if (empty($rfq)) {
            return response()->json([
                'status' => false,
                'message' => 'RFQ not found',
            ]);
        }

        $product = DB::table('products')->where('id', $first_product_id)->first();
        if (empty($product)) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ]);
        }

        $vendors = DB::table('users')
                    ->select('users.id', 'vendors.country', 'vendors.nature_of_business as dealer_type')
                    ->join('vendors', 'vendors.user_id', '=', 'users.id')
                    ->whereIn('users.id', $vendor_array)
                    ->where('users.status', 1)
                    ->where('users.is_verified', 1)
                    ->where('users.user_type', 2)
                    ->get();

        // echo "<pre>";
        // print_r($vendors);
        // die;
        if ($vendors->count() !== count($vendor_array)) {
            return response()->json([
                'status' => false,
                'message' => 'One or more vendors not found',
            ]);
        }

        $vendor_data = [];
        foreach ($vendors as $key => $value) {
            $vendor_data[$value->id] = ['country'=>$value->country, 'dealer_type'=>$value->dealer_type];
        }

        $vendor_products = DB::table('vendor_products')
            ->select('vendor_id')
            ->whereIn('vendor_id', $vendor_array)
            ->where('product_id', $first_product_id)
            // ->orwhere('id', 70861)
            ->pluck('vendor_id')->toArray();


        $vendor_product_data = [];
        $rfq_vendors_data = [];

        DB::beginTransaction();

        try {
            foreach ($vendor_array as $key => $vendor_id) {
                if(!empty($vendor_products) && in_array($vendor_id, $vendor_products)){
                    DB::table('vendor_products')->update([
                        'vendor_status' => 1,
                        'edit_status' => 0,
                        'approval_status' => 1,
                        'added_from' => 2
                    ]);
                }else{
                    $vendor_product_data[] = [
                        'vendor_id' => $vendor_id,
                        'product_id' => $product->id,
                        'description' => $product->product_name,
                        'dealer_type_id' => ($vendor_data[$vendor_id]['dealer_type']==1 ? $vendor_data[$vendor_id]['dealer_type'] : 2),
                        'gst_id' => ($vendor_data[$vendor_id]['country']=='101' ? 4 : 1), //4 for 18% and 1 for 0%
                        'vendor_status' => 1, // active
                        'edit_status' => 0,
                        'approval_status' => 1,
                        'added_from' => 2,
                        'added_by_user_id' => Auth::user()->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $rfq_vendors_data[] = [
                    'rfq_id' => $rfq_id,
                    'vendor_user_id' => $vendor_id,
                    'product_id' => $first_product_id,
                    'vendor_status' => 1
                ];
            }
            if(!empty($vendor_product_data)){
                DB::table('vendor_products')->insert($vendor_product_data);
            }
            if(!empty($rfq_vendors_data)){
                DB::table('rfq_vendors')->insert($rfq_vendors_data);
            }

            DB::commit();

            // Log::info('Product added to vendor successfully, Vendors: '.json_encode($vendor_array));
            // Log::info('Product added to vendor successfully, Products: '.json_encode($vendor_data));
            // Log::info('Product added to vendor successfully, Vendor Products: '.json_encode($vendor_products));

            return response()->json([
                'status' => true,
                'message' => 'Product added to vendor successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'There was an error adding the product. Please try again. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }


    }

}
