<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\BuyerPreference;
class VendorSearchController extends Controller
{
    public function index()
    {
        return view('buyer.search-vendor.index');
    }

    public function search(Request $request)
    {
        $search = $request->search;
        $vendors=Vendor::with(['vendor_products.product'=>function($q){
            $q->select('id','product_name');
            $q->limit(3);
        }])->where('legal_name', 'like', '%' . $search . '%')->get();
        $html = '';
        if($vendors->count() > 0){
            foreach($vendors as $vendor){
                $check = BuyerPreference::where('buyer_user_id', getParentUserId())->where('vend_user_id', $vendor->user_id)->first();

            $html .= '<li>
                        <p class="w-100">
                            <a href="'.route('webPage.index',['vendorId'=>base64_encode($vendor->user_id)]).'" class="text-blue  vendor-name-left" title="' . $vendor->legal_name . '">' . $vendor->legal_name . '</a>
                            <a href="javascript:void(0);" title="'.($vendor->vendor_products->count() > 0 ? $vendor->vendor_products->pluck('product.product_name')->filter()->join(', ') : '').'">'.($vendor->vendor_products->count() > 0 ? $vendor->vendor_products->pluck('product.product_name')->filter()->join(', '): '').'</a>
                        </p>
                        <div class="d-flex mng-icons" data-id="'.$vendor->user_id.'">';
                if(!empty($check)&&$check->fav_or_black == '1'){
            $html .= '<button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                            <span class="bi bi-heart-fill" aria-hidden="true"></span>
                        </button>';
                }elseif(!empty($check)&&$check->fav_or_black == '2'){
            $html .= '<button type="button" class="like-btn bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                            <span class="bi bi-ban-fill" aria-hidden="true"></span>
                        </button>';
                }else{
            $html .='   <button type="button" class="like-btn bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                            <span class="bi bi-heart" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                            <span class="bi bi-ban" aria-hidden="true"></span>
                        </button>';
                }
            $html .='   </div>
                    </li>';
            }
        }else{
            $html = '<li><p class="green-text">No vendor found.</p></li>';
        }

        return response()->json($html);
    }

    public function favouriteBlockVendor(Request $request)
    {
        $vendorId = $request->vendor_id;
        $types = $request->types;
        $html='';
        $vendor = BuyerPreference::where('buyer_user_id', getParentUserId())->where('vend_user_id', $vendorId)->first();
        if(!empty($vendor)){
            $vendor->delete();
            $html='<button type="button" class="like-btn bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                        <span class="bi bi-heart" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                        <span class="bi bi-ban" aria-hidden="true"></span>
                    </button>';
        }else{
            $data=new BuyerPreference();
            $data->buyer_user_id = getParentUserId();
            $data->vend_user_id = $vendorId;
            $data->fav_or_black = $types=='favorite' ? 1 : 2;
            $data->save();
            if($types=='favorite'){
                $html = '<button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                            <span class="bi bi-heart-fill" aria-hidden="true"></span>
                        </button>';
            }else{
                $html = '<button type="button" class="like-btn bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                            <span class="bi bi-ban-fill" aria-hidden="true"></span>
                        </button>';
            }
        }
        return response()->json($html);
    }
}