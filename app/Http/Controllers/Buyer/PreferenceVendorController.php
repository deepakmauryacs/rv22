<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BuyerPreference;
use App\Traits\HasModulePermission;
class PreferenceVendorController extends Controller
{
    use HasModulePermission;

    //favourite
    public function favourite(Request $request)
    {
        $this->ensurePermission('FAVOURITE_VENDORS', 'view', '1');
        $query = BuyerPreference::with([
            'user.vendor' => function ($q) {
                // $q->select('id', 'legal_name'); // Make sure to include 'id'
            },
            'user.vendor.vendor_products.product' => function ($q) {
                $q->select('id', 'product_name'); // Include 'id' to preserve relationship
                $q->limit(3);
            }
        ])->where('buyer_user_id', getParentUserId());
        $query->where('fav_or_black', '1');
        $order = $request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        
        if ($request->ajax()) {
            return view('buyer.vendor.partials.favourite-table', compact('results'))->render();
        }
        return view('buyer.vendor.favourite', compact('results'));
    }
    public function blacklist(Request $request)
    {
        $this->ensurePermission('BLACKLISTED_VENDORS', 'view', '1');
        $query = BuyerPreference::with([
            'user.vendor' => function ($q) {
                // $q->select('id', 'legal_name'); // Make sure to include 'id'
            },
            'user.vendor.vendor_products.product' => function ($q) {
                $q->select('id', 'product_name'); // Include 'id' to preserve relationship
                $q->limit(3);
            }
        ])->where('buyer_user_id', getParentUserId());
        $query->where('fav_or_black', '2');
        $order = $request->order;
        if (!empty($order)) {
            $query->orderBy($column[$order['0']['column']], $order['0']['dir']);
        } else {
            $query->orderBy('id', 'desc');
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        
        if ($request->ajax()) {
            return view('buyer.vendor.partials.blacklist-table', compact('results'))->render();
        }
        return view('buyer.vendor.blacklist', compact('results'));
    }
    public function deleted(Request $request,$id)
    {
        $this->ensurePermission('BLACKLISTED_VENDORS', 'delete', '1');
        BuyerPreference::where('buyer_user_id', getParentUserId())->where('id', $id)->delete();
        return response()->json(['status' => true,'message' => 'Deleted Successfully']);
    }
}
