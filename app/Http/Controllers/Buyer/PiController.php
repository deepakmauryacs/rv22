<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrdersPi;
class PiController extends Controller
{
    public function index(Request $request)
    {
        $user_branch_id_only = getBuyerUserBranchIdOnly();

        $query=OrdersPi::with('vendor:id,user_id,legal_name')->where('buyer_id', getParentUserId());
        if ($request->filled('order_no')) {
            $query->where('order_number', 'like', '%' . $request->input('order_no') . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('legal_name', 'like', '%' . $request->input('vendor_name') . '%');
            });
        }
        if ($request->filled('form_date')) {
            $form_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('form_date'))->format('Y-m-d 00:00:00');
            $query->where('created_at', '>=', $form_date);
        }
        if ($request->filled('to_date')) {
            $to_date = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('to_date'))->format('Y-m-d 23:59:59');
            $query->where('created_at', '<=', $to_date);
        }
        if(!empty($user_branch_id_only)){
            $query->whereIn('buyer_branch_id', $user_branch_id_only);
        }
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());
        
        if ($request->ajax()) {
            return view('buyer.rfq.pi.partials.table', compact('results'))->render();
        }
        return view('buyer.rfq.pi.index', compact('results'));
    }
}
