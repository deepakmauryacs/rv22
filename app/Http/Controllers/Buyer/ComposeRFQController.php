<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\RfqVendor;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Support\Facades\Session;
// use App\Helpers\EmailHelper;
use Carbon\Carbon;
use App\Traits\HasModulePermission;

class ComposeRFQController extends Controller
{
    use HasModulePermission;

    function composeRFQ(Request $request) {
        $this->ensurePermission('GENERATE_NEW_RFQ', 'add', '1');
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

        $is_vendor_exists = $this->validateProductVendors($draft_id);
        if($is_vendor_exists==false){
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => 'Please select vendor for each product.',
            ]);
        }
        
        $is_draft_valid = $this->validateDraftProducts($is_draft_exists);
        if ($is_draft_valid != "Verified") {
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => $is_draft_valid,
            ]);
        }

        DB::beginTransaction();

        try {
            $rfq_number = generateBuyerRFQNumber($company_id);
            $rfq_number_status = validateRFQNumber($rfq_number);
            if($rfq_number_status==false){
                return response()->json([
                    'status' => false,
                    'type' => "UpdateBranchAndReload",
                    'message' => "Duplicate RFQ Number Found.",
                ]);
            }
            
            // add product to order tables:START
            $date = date('Y-m-d H:i:s');
            if (!empty($is_draft_exists->scheduled_date)) {
                $rfq_status = 2;
            } else {
                $rfq_status = 1;
            }

            $rfq = Rfq::find($is_draft_exists->id);
            $rfq->rfq_id = $rfq_number;
            $rfq->buyer_user_id = Auth::user()->id;
            $rfq->record_type = 2;
            $rfq->buyer_rfq_status = $rfq_status;
            $rfq->save();

            RfqProduct::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
            RfqProductVariant::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);
            RfqVendor::where('rfq_id', $is_draft_exists->rfq_id)->update(['rfq_id'=>$rfq_number]);

            if (empty($is_draft_exists->scheduled_date)) {
                $rfq_vendors = RfqVendor::with(
                                'rfqVendorProfile:id,user_id,legal_name',
                                'rfqVendorDetails:id,name,email'
                            )
                            ->where('rfq_id', $rfq_number)
                            ->groupBy('vendor_user_id')
                            ->select('vendor_user_id')
                            ->get();

                $vendor_details = [];

                foreach ($rfq_vendors as $vendor) {
                    // Make sure the relationships exist
                    if ($vendor->rfqVendorProfile && $vendor->rfqVendorDetails) {
                        $userId = $vendor->rfqVendorProfile->user_id;

                        $vendor_details[$userId] = [
                            'legal_name' => $vendor->rfqVendorProfile->legal_name,
                            'email'      => $vendor->rfqVendorDetails->email,
                        ];
                    }
                }

                $notification_data = array();
                $notification_data['rfq_no'] = $rfq_number;
                $notification_data['message_type'] = 'RFQ Received';
                $notification_data['notification_link'] = route("vendor.rfq.reply", ["rfq_id"=> $rfq_number]);//route will change after RFQ Details page created on vendor side
                $notification_data['to_user_id'] = array_keys($vendor_details);
                $status = sendNotifications($notification_data);
                
                $this->sendEmailToVendors($rfq_number, $vendor_details);

            }
            
            // echo "<pre>";
            // echo $rfq_number;
            // die;

            setSessionWithExpiry('rfq_number', $rfq_number);

            DB::commit();

            return response()->json([
                'status' => true,
                'redirect_url' => route("buyer.rfq.compose-rfq-success", [$rfq_number]),
                'message' => 'RFQ Generated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Generate RFQ. '.$e->getMessage(),
                'complete_message' => $e
            ]);
        }

    }

    private function isDraftExists($draft_id, $company_id, $record_type=1){
        return DB::table('rfqs')
                    ->where('rfq_id', $draft_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', $record_type)
                    ->first();
    }
    private function sendEmailToVendors($rfq_number, $vendor_details){
        $subject = "RFQ Received (RFQ No." . $rfq_number . " )";

        $mail_data = vendorEmailTemplet('user-success-order');
        $admin_msg = $mail_data->mail_message;

        $admin_msg = str_replace('$rfqdate', now()->format('d M Y'), $admin_msg);
        $admin_msg = str_replace('$rfq_date_formate', now()->format('d/m/Y'), $admin_msg);
        $admin_msg = str_replace('$rfq_number', $rfq_number, $admin_msg);
        $admin_msg = str_replace('$buyer_name', session('legal_name'), $admin_msg);
        $admin_msg = str_replace('$website_url', route("login"), $admin_msg);

        $mail_arr = array();
        foreach ($vendor_details as $key => $value) {
            $new_admin_msg = $admin_msg;

            $vendor_leagl_name = $value['legal_name'];
            $vendor_email = $value['email'];
            $new_admin_msg = str_replace('$vendor_name', $vendor_leagl_name, $new_admin_msg);

            $mail_arr[] = array('subject' => $subject, 'body' => $new_admin_msg, 'to' => $vendor_email);
        }
        sendMultipleDBEmails($mail_arr);
    }
    private function validateProductVendors($draft_id){
        
        $draft_products = DB::table('rfq_products')->where("rfq_id", $draft_id)->select("product_id")->pluck("product_id")->toArray();
        
        $products_vendors = Product::with([
                        'product_vendors' => function ($q) {
                            $q->select('vendor_products.id', 'vendor_products.vendor_id', 'vendor_products.product_id')
                            ->where('vendor_status', 1)
                            ->where('edit_status', 0)
                            ->where('approval_status', 1);

                            $q->whereHas('vendor_profile', function ($q) {
                                $q->whereNotNull('vendor_code')
                                    ->whereHas('user', function ($q) {
                                    $q->where('status', 1)
                                        ->where('is_verified', 1)
                                        ->where('user_type', 2);
                                });
                            });
                        },
                        'product_vendors.vendor_profile:id,user_id,vendor_code',
                        'product_vendors.vendor_profile.user:id',
                    ])
                    ->select("id")
                    ->whereIn("id", $draft_products)
                    ->where("status", 1)
                    ->get();

        $draft_vendors = DB::table('rfq_vendors')->where("rfq_id", $draft_id)->select("vendor_user_id")->pluck("vendor_user_id")->toArray();

        $productVendorsArray = [];
        foreach ($products_vendors as $product) {
            $vendorIds = $product->product_vendors->pluck('vendor_id')->all();

            $matchingVendorIds = array_values(array_intersect($vendorIds, $draft_vendors));

            if (!empty($matchingVendorIds)) {
                $productVendorsArray[$product->id] = $matchingVendorIds;
            }
        }

        $hasEmpty = collect($productVendorsArray)->contains(function ($vendors) {
            return empty($vendors);
        });

        unset($draft_products);
        unset($products_vendors);
        unset($draft_vendors);
        unset($productVendorsArray);

        if ($hasEmpty) {
            return false;
        }else{
            return true;
        }
    }
    
    private function validateDraftProducts($draft_data){
        if (empty($draft_data->buyer_branch) || $draft_data->buyer_branch == 0 ) {
            return 'Buyer Branch is required';
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        $branch_names = getBuyerBranchs($user_branch_id_only);
        $is_branch_available = !empty(array_filter($branch_names, fn($item) => $item->branch_id == $draft_data->buyer_branch));
        unset($branch_names);
        if($is_branch_available==false){
            return 'Buyer Branch is not active to generate the RFQ';
        }
        unset($factory_names);
        unset($user_branch_id_only);

        if (!empty($draft_data->last_response_date) && date("Y-m-d", strtotime($draft_data->last_response_date)) < date("Y-m-d", strtotime("+1 days"))) {
            return 'Last Response Date can not be less than tomorrow date';
        }

        if (!empty($draft_data->scheduled_date) && date("Y-m-d", strtotime($draft_data->scheduled_date)) < date("Y-m-d", strtotime("+1 days"))) {
            $edit_rfq_id = $draft_data->edit_rfq_id;
            $is_original_rfq = array();

            if(!empty($edit_rfq_id)){
                $company_id = getParentUserId();
                $is_original_rfq = DB::table('rfqs')
                    ->select('buyer_rfq_status')
                    ->where('rfq_id', $edit_rfq_id)
                    ->where('buyer_id', $company_id)
                    ->where('record_type', 2)
                    ->first();
            }
            if(!empty($is_original_rfq)){
                if($is_original_rfq->buyer_rfq_status==2){
                    return 'Scheduled Date can not be less than tomorrow date.';
                }
            }else{
                return 'Scheduled Date can not be less than tomorrow date.';
            }
        }

        if (!empty($draft_data->last_response_date) && !empty($draft_data->scheduled_date) && date("Y-m-d", strtotime($draft_data->last_response_date)) <= date("Y-m-d", strtotime($draft_data->scheduled_date))) {
            return 'Last Response Date can not be less than or equal to scheduled date.';
        }

        $variants = DB::table('rfq_product_variants')->select('quantity', 'uom')->where('rfq_id', $draft_data->rfq_id)->get();
        $hasQuantityBlank = $variants->contains(function ($variant) {
            return empty($variant->quantity);
        });
        $hasUOMBlank = $variants->contains(function ($variant) {
            return empty($variant->uom);
        });
        unset($variants);
        $is_verified = "Verified";
        if ($hasQuantityBlank || $hasUOMBlank) {
            $field = '';
            if ($hasQuantityBlank) {
                $field .= 'Quantity';
            }
            if ($hasUOMBlank) {
                $field .= !empty($field) ? ' and UOM' : ' UOM';
            }
            $is_verified = 'Product ' . $field . ' is manadatory.';
        }
        return $is_verified;
    }
    
    function composeRFQSuccess(Request $request, $rfq_number) {
        $this->ensurePermission('GENERATE_NEW_RFQ', 'view', '1');
        if($rfq_number!=getSessionWithExpiry('rfq_number')){
            session()->flash('error', "Page has expired.");
            return redirect()->route('buyer.dashboard');
        }
        $rfq = DB::table('rfqs')->where('rfq_id', $rfq_number)->first();
        if(empty($rfq)){
            session()->flash('error', "Page not found");
            return redirect()->route('buyer.dashboard');
        }
        $rfq_type = 'Sent';
        if(!empty($rfq->scheduled_date) && date("Y-m-d", strtotime($rfq->scheduled_date)) > date("Y-m-d")){
            $rfq_type = 'Scheduled';
        }

        return view('buyer.rfq.rfq-success', compact('rfq', 'rfq_type'));
    }

    function updateRFQ(Request $request) {
        $this->ensurePermission('EDIT_RFQ', 'edit', '1');
        $draft_id = $request->rfq_draft_id;
        $company_id = getParentUserId();

        $is_draft_exists = $this->isDraftExists($draft_id, $company_id, 3);
        if(empty($is_draft_exists)){
            return response()->json([
                'status' => false,
                'type' => "DraftNotFound",
                'message' => 'Products from this tab has already been processed.',
            ]);
        }

        $is_vendor_exists = $this->validateProductVendors($draft_id);
        if($is_vendor_exists==false){
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => 'Please select vendor for each product.',
            ]);
        }
        
        $is_draft_valid = $this->validateDraftProducts($is_draft_exists);
        if ($is_draft_valid != "Verified") {
            return response()->json([
                'status' => false,
                'type' => "UpdateBranchAndReload",
                'message' => $is_draft_valid,
            ]);
        }

        $draft_products = DB::table('rfq_products')->select('id', 'product_id', 'brand', 'remarks')->where("rfq_id", $draft_id)->get();
        $draft_variants = DB::table('rfq_product_variants')->select('id', 'specification', 'quantity', 'size', 'uom', 'attachment', 'edit_id')->where("rfq_id", $draft_id)->get();
        $draft_vendors = DB::table('rfq_vendors')->select('id', 'vendor_user_id', 'product_id', 'vendor_status')->where("rfq_id", $draft_id)->get();

        $edit_rfq_id = $is_draft_exists->edit_rfq_id;

        $rfq_data = DB::table('rfqs')->where("rfq_id", $edit_rfq_id)->first();
        $rfq_products = DB::table('rfq_products')->select('id', 'product_id')->where("rfq_id", $edit_rfq_id)->get()->keyBy('product_id');
        $rfq_variants = DB::table('rfq_product_variants')->select('id', 'edit_id')->where("rfq_id", $edit_rfq_id)->get()->keyBy('id');
        $rfq_vendors = DB::table('rfq_vendors')
                        ->select('id', 'vendor_user_id', 'product_id', 'vendor_status')
                        ->where('rfq_id', $edit_rfq_id)
                        ->get()
                        ->keyBy(function($item) {
                            return $item->vendor_user_id . '_' . $item->product_id;
                        });
        // 

        $draft_vendor_ids = $draft_vendors->pluck('vendor_user_id')->unique()->toArray();
        $rfq_vendor_ids = $rfq_vendors->pluck('vendor_user_id')->unique()->toArray();
        $new_vendor_ids = array_values(array_diff($draft_vendor_ids, $rfq_vendor_ids));
        // $removed_vendor_ids = array_diff($rfq_vendor_ids, $draft_vendor_ids);
        $old_vendor_ids = array_values(array_intersect($rfq_vendor_ids, $draft_vendor_ids));

        $draft_product_ids = $draft_products->pluck('product_id')->toArray();
        $actual_product_ids = $rfq_products->keys()->toArray();
        $products_to_delete = array_diff($actual_product_ids, $draft_product_ids);

        $draft_variant_edit_ids = $draft_variants->pluck('edit_id')->filter()->toArray();
        $actual_variant_ids = $rfq_variants->keys()->toArray();
        $variants_to_delete = array_diff($actual_variant_ids, $draft_variant_edit_ids);

        $draft_vendor_keys = $draft_vendors->map(function($item) {
                return $item->vendor_user_id . '_' . $item->product_id;
            })->toArray();
        $actual_vendor_keys = $rfq_vendors->keys()->toArray();
        $vendors_to_delete = array_diff($actual_vendor_keys, $draft_vendor_keys);

        $quotationsToDelete = DB::table('rfq_vendor_quotations')
            ->where('rfq_id', $edit_rfq_id)
            ->whereIn('vendor_id', $vendors_to_delete)
            ->whereIn('rfq_product_variant_id', $variants_to_delete)
            ->get();
        // 
        if (!empty($vendors_to_delete) || !empty($variants_to_delete)) {
            $quotationIds = DB::table('rfq_vendor_quotations')
                ->where('rfq_id', $edit_rfq_id)
                ->when(!empty($vendors_to_delete) && !empty($variants_to_delete), function ($query) use ($vendors_to_delete, $variants_to_delete) {
                    $query->where(function ($query2) use ($vendors_to_delete, $variants_to_delete) {
                        $query2->whereIn('vendor_id', $vendors_to_delete)
                            ->orWhereIn('rfq_product_variant_id', $variants_to_delete);
                    });
                })
                ->when(!empty($vendors_to_delete) && empty($variants_to_delete), function ($query) use ($vendors_to_delete) {
                    $query->whereIn('vendor_id', $vendors_to_delete);
                })
                ->when(empty($vendors_to_delete) && !empty($variants_to_delete), function ($query) use ($variants_to_delete) {
                    $query->whereIn('rfq_product_variant_id', $variants_to_delete);
                })
                ->pluck('vendor_attachment_file', 'id')
                ->toArray();
        }
        $rfq_update = [
            'prn_no' => $is_draft_exists->prn_no,
            'buyer_branch' => $is_draft_exists->buyer_branch,
            'last_response_date' => $is_draft_exists->last_response_date,
            'buyer_price_basis' => $is_draft_exists->buyer_price_basis,
            'buyer_pay_term' => $is_draft_exists->buyer_pay_term,
            'buyer_delivery_period' => $is_draft_exists->buyer_delivery_period,
            'warranty_gurarantee' => $is_draft_exists->warranty_gurarantee,
            'edit_by' => Auth::user()->id,
            'edit_rfq_id' => NULL,
            'scheduled_date' => $is_draft_exists->scheduled_date,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // echo "<pre>";
        // print_r($old_vendor_ids);
        // die;

        DB::beginTransaction();

        try {
            // rfq_products
            $this->editRFQProduct($edit_rfq_id, $products_to_delete, $draft_products, $rfq_products);
            unset($products_to_delete);
            unset($draft_products);
            unset($rfq_products);
            
            // rfq_product_variants
            $this->editRFQProductVariant($edit_rfq_id, $variants_to_delete, $draft_variants, $rfq_variants);
            unset($variants_to_delete);
            unset($draft_variants);
            unset($rfq_variants);
            
            // rfq_vendors
            $this->editRFQVendors($edit_rfq_id, $rfq_vendors, $vendors_to_delete, $draft_vendors);
            unset($rfq_vendors);
            unset($vendors_to_delete);
            unset($draft_vendors);

            if (!empty($quotationIds)) {
                DB::table('rfq_vendor_quotations')
                    ->whereIn('id', array_keys($quotationIds))
                    ->delete();
            }

            // rfqs
            DB::table('rfqs')
                ->where('rfq_id', $edit_rfq_id)
                ->update($rfq_update);

            // notifications & emails
            if (empty($is_draft_exists->scheduled_date)) {
                $this->editRFQEmailAndNotifications($edit_rfq_id, $new_vendor_ids, $old_vendor_ids);
                unset($new_vendor_ids);
                unset($old_vendor_ids);
            }

            DB::table('rfqs')->where("rfq_id", $draft_id)->delete();
            DB::table('rfq_products')->where("rfq_id", $draft_id)->delete();
            DB::table('rfq_product_variants')->where("rfq_id", $draft_id)->delete();
            DB::table('rfq_vendors')->where("rfq_id", $draft_id)->delete();
            
            if (!empty($quotationIds)) {
                foreach ($quotationIds as $id => $attachment_file) {
                    if(!empty($attachment_file) && is_file(public_path('uploads/rfq_product/sub_products/'.$attachment_file))){
                        removeFile(public_path('uploads/rfq_product/sub_products/'.$attachment_file));
                    }
                }
            }
            unset($quotationIds);

            setSessionWithExpiry('rfq_number', $edit_rfq_id);
            
            DB::commit();

            return response()->json([
                'status' => true,
                'redirect_url' => route("buyer.rfq.compose-rfq-success", [$edit_rfq_id]),
                'message' => 'RFQ Updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to Update RFQ. '.$e->getMessage(),
                'complete_message' => [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'code'    => $e->getCode(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                ],
            ]);
        }
    }

    private function editRFQProduct($edit_rfq_id, $products_to_delete, $draft_products, $rfq_products){
        if (!empty($products_to_delete)) {
            DB::table('rfq_products')
                ->where('rfq_id', $edit_rfq_id)
                ->whereIn('product_id', $products_to_delete)
                ->delete();
        }
        unset($products_to_delete);
        $products_draft_to_rfq = [];
        foreach ($draft_products as $product) {
            if (isset($rfq_products[$product->product_id])) {
                DB::table('rfq_products')
                    ->where('rfq_id', $edit_rfq_id)
                    ->where('product_id', $product->product_id)
                    ->update([
                        'brand' => $product->brand,
                        'remarks' => $product->remarks
                    ]);
            } else {
                $products_draft_to_rfq[] = $product->id;
            }
        }
        unset($draft_products);
        unset($rfq_products);
        if(!empty($products_draft_to_rfq)){
            DB::table('rfq_products')
                ->whereIn('id', $products_draft_to_rfq)
                ->update(['rfq_id' => $edit_rfq_id]);
        }
        unset($products_draft_to_rfq);
    }
    private function editRFQProductVariant($edit_rfq_id, $variants_to_delete, $draft_variants, $rfq_variants){
        if (!empty($variants_to_delete)) {
            DB::table('rfq_product_variants')
                ->where('rfq_id', $edit_rfq_id)
                ->whereIn('id', $variants_to_delete)
                ->delete();
        }
        unset($variants_to_delete);
        $product_variants_draft_to_rfq = [];
        foreach ($draft_variants as $variant) {
            if ($variant->edit_id && isset($rfq_variants[$variant->edit_id])) {
                DB::table('rfq_product_variants')
                    ->where('rfq_id', $edit_rfq_id)
                    ->where('id', $variant->edit_id)
                    ->update([
                        'specification' => $variant->specification,
                        'quantity' => $variant->quantity,
                        'size' => $variant->size,
                        'uom' => $variant->uom,
                        'attachment' => $variant->attachment
                    ]);
            } else {
                $product_variants_draft_to_rfq[] = $variant->id;
            }
        }
        unset($draft_variants);
        unset($rfq_variants);
        if(!empty($product_variants_draft_to_rfq)){
            DB::table('rfq_product_variants')
                ->whereIn('id', $product_variants_draft_to_rfq)
                ->update(['rfq_id' => $edit_rfq_id]);
        }
        unset($product_variants_draft_to_rfq);
    }

    private function editRFQVendors($edit_rfq_id, $rfq_vendors, $vendors_to_delete, $draft_vendors){
        
        $vendor_rfq_status = [];
        foreach ($rfq_vendors as $key => $vendor) {
            $vendor_rfq_status[$vendor->vendor_user_id] = $vendor->vendor_status;
        }
        if (!empty($vendors_to_delete)) {
            foreach ($vendors_to_delete as $key) {
                list($vendor_user_id, $product_id) = explode('_', $key);
                DB::table('rfq_vendors')
                    ->where('rfq_id', $edit_rfq_id)
                    ->where('vendor_user_id', $vendor_user_id)
                    ->where('product_id', $product_id)
                    ->delete();
            }
        }
        unset($vendors_to_delete);
        $vendor_draft_to_rfq = [];
        $vendor_update_rfq_status = [];
        foreach ($draft_vendors as $vendor) {
            $key = $vendor->vendor_user_id . '_' . $vendor->product_id;
            if (!isset($rfq_vendors[$key])) {
                $vendor_draft_to_rfq[] = $vendor->id;

                $vendor_status = 1;
                if(isset($vendor_rfq_status[$vendor->vendor_user_id])){
                    $vendor_status = $vendor_rfq_status[$vendor->vendor_user_id];
                }
                $vendor_update_rfq_status[$vendor_status][] = $vendor->vendor_user_id;
            }
        }
        unset($draft_vendors);
        unset($rfq_vendors);
        unset($vendor_rfq_status);
        if(!empty($vendor_draft_to_rfq)){
            DB::table('rfq_vendors')
                ->whereIn('id', $vendor_draft_to_rfq)
                ->update(['rfq_id' => $edit_rfq_id]);
        }
        unset($vendor_draft_to_rfq);
        if(!empty($vendor_update_rfq_status)){
            foreach ($vendor_update_rfq_status as $vend_rfq_status => $vendor_ids) {
                DB::table("rfq_vendors")
                    ->where('rfq_id', $edit_rfq_id)
                    ->whereIn('vendor_user_id', array_values($vendor_ids))
                    ->update(['vendor_status' => $vend_rfq_status]);
            }
        }
        unset($vendor_update_rfq_status);
    }

    private function editRFQEmailAndNotifications($edit_rfq_id, $new_vendor_ids, $old_vendor_ids){
        if(!empty($new_vendor_ids)){
            $vendors = Vendor::with(
                        'user:id,email'
                    )
                    ->select('id', 'user_id', 'legal_name')
                    ->whereIn('user_id', $new_vendor_ids)
                    ->get()->toArray();

            $vendor_details = [];
            foreach ($vendors as $vendor) {
                $vendor_details[$vendor['user_id']] = [
                    'legal_name' => $vendor['legal_name'],
                    'email'      => $vendor['user']['email'],
                ];
            }

            $notification_data = array();
            $notification_data['rfq_no'] = $edit_rfq_id;
            $notification_data['message_type'] = 'RFQ Received';
            $notification_data['notification_link'] = route("vendor.rfq.reply", ["rfq_id"=> $edit_rfq_id]);
            $notification_data['to_user_id'] = $new_vendor_ids;
            $status = sendNotifications($notification_data);
            unset($new_vendor_ids);

            $this->sendEmailToVendors($edit_rfq_id, $vendor_details);
        }
        
        if(!empty($old_vendor_ids)){
            $notification_data = array();
            $notification_data['rfq_no'] = $edit_rfq_id;
            $notification_data['message_type'] = 'RFQ Edited';
            $notification_data['notification_link'] = route("vendor.rfq.reply", ["rfq_id"=> $edit_rfq_id]);
            $notification_data['to_user_id'] = $old_vendor_ids;
            $status = sendNotifications($notification_data);
            unset($old_vendor_ids);
        }
    }

}
