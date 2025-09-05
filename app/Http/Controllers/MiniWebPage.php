<?php

namespace App\Http\Controllers;

use App\Models\BuyerPreference;
use App\Models\Product;
use App\Models\Rfq;
use App\Models\RfqProduct;
use App\Models\RfqProductVariant;
use App\Models\User;
use App\Models\VendorProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiniWebPage extends Controller
{
    public function index($id)
    {
        $id = base64_decode($id);

        // dd($id);
        $vendorCompanyName = User::with(['vendor'])->where('id', $id)->first();

        if (!$vendorCompanyName) {
            return back()->with('error', 'This is not valid vendor.');
        }


        if (!$vendorCompanyName->vendor) {
            $user_type = '';
            if ($vendorCompanyName->user_type == 1) {
                $user_type = 'buyer';
            } elseif ($vendorCompanyName->user_type == 2) {
                $user_type = 'vendor';
            } elseif ($vendorCompanyName->user_type == 3) {
                $user_type = 'super-admin';
            }
            return redirect()->route($user_type . '.dashboard')->with('error', 'Vendor not found.');
        }

        if ($vendorCompanyName->is_verified != 1) {
            return back()->with('error', 'This is not verified vendor.');
        }



        session()->put('vendorId', $id);
        $companySlug = createSlug($vendorCompanyName->vendor->legal_name);
        session()->put('company_slug', $companySlug);

        return redirect()->to(route('webPage.home', ['companyName' => $companySlug]));
    }


    /*public  function createSlug($string)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }*/


    public function home($companyName)
    {
        /***:- validate the company name with session  -:***/
        $companySlug = session()->get('company_slug');
        if ($companyName !== $companySlug) {
            return redirect()->route('vendor.dashboard')->with('error', 'Invalid vendor company name.');
        }

        /***:- check the vendor exist  -:***/
        $vendorId = session()->get('vendorId');
        $vendorInfo = User::with(['vendor', 'vendorWebPage'])->where('id', $vendorId)->first();
        if (!$vendorInfo) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $html = $this->favBtn($vendorId);
        return view('web-page.index', compact('vendorInfo', 'html'));
    }


    public function productDetail(Request $request, $companyName, $productId)
    {

        $companySlug = session()->get('company_slug');
        if ($request->has('p')) {
            session()->put('p', base64_decode($request->get('p')));
            return redirect()->to(route('webPage.productDetail', ['companyName' => session()->get('company_slug'), 'productId' => $companySlug]));
        }

        $productId = session()->get('p');

        /***:- validate the company name with session  -:***/
        if ($companyName !== $companySlug) {
            return redirect()->route('vendor.dashboard')->with('error', 'Invalid vendor company name.');
        }

        /***:- check the vendor exist  -:***/
        $vendorId = session()->get('vendorId');
        $vendorInfo = User::with(['vendor', 'vendorWebPage'])->where('id', $vendorId)->first();
        if (!$vendorInfo) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $productImages = VendorProduct::with(['gallery'])
            ->where('vendor_id', $vendorId)
            ->where('id', $productId)->first();


        if (!$productImages) {
            return response()
                ->view('errors.product_not_found', [], 404);
            //return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }

        $pID = $productImages->product_id;

        return view('web-page.vendor-product-detail', compact('vendorInfo', 'productImages', 'pID'));
    }

    public function getVendorProduct(Request $request)
    {

        $vendorId = session()->get('vendorId');

        $page = $request->input('page', 1);
        $perPage = 12;

        $products = VendorProduct::with(['product'])
            ->where('vendor_id', $vendorId)
            ->where('vendor_status', 1)
            ->where('edit_status', 0)
            ->where('approval_status', 1)
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->count() > 0) {
            $product_html = view('web-page.vendor-product-list', compact('products'))->render();
            return response()->json([
                'status' => true,
                'message' => 'Vendor Product found',
                'products' => $product_html,
                'hasMore'  => $products->hasMorePages()
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No Product Found.'
            ]);
        }
    }

    public function contactUs($companyName)
    {


        /***:- validate the company name with session  -:***/
        $companySlug = session()->get('company_slug');
        if ($companyName !== $companySlug) {
            return redirect()->route('vendor.dashboard')->with('error', 'Invalid vendor company name.');
        }

        /***:- check the vendor exist  -:***/
        $vendorId = session()->get('vendorId');
        $vendorInfo = User::with(['vendor', 'vendorWebPage'])->where('id', $vendorId)->first();
        if (!$vendorInfo) {
            return redirect()->route('vendor.dashboard')->with('error', 'Vendor not found.');
        }



        $html = $this->favBtn($vendorId);
        return view('web-page.contact-us', compact('vendorInfo', 'vendorId', 'html'));
    }

    public function favBtn($vendorId)
    {
        $check = BuyerPreference::where('buyer_user_id', getParentUserId(Auth::user()->id))->where('vend_user_id',  $vendorId)->first();
        $html = '';
        if (!empty($check) && $check->fav_or_black == '1') {

            $html .= '<button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                            <span class="like-icon cursor-pointer font-size-18 bi bi-heart-fill" aria-hidden="true"></span>
                        </button>';
        } elseif (!empty($check) && $check->fav_or_black == '2') {
            $html .= '<button type="button" class=" bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                            <span class="dislike-icon cursor-pointer font-size-18 bi bi-ban-fill" aria-hidden="true"></span>
                        </button>';
        } else {
            $html .= ' <button type="button" class="like-btn bg-transparent border-0 p-0" onclick="manageVendor(this,`favorite`);">
                            <span class="like-icon cursor-pointer font-size-18  bi bi-heart" aria-hidden="true"></span>
                        </button>
                        <button type="button" class="bg-transparent border-0 p-0" onclick="manageVendor(this,`ban`);">
                            <span class="dislike-icon cursor-pointer font-size-14 bi bi-ban" aria-hidden="true"></span>
                        </button>';
        }

        return $html;
    }
}
