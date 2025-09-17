<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\VendorProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class VendorDashboardController extends Controller
{
    public function index()
    {
        // You can pass user data to the view
        $user = Auth::user();
        $recentProducts = collect();

        if ($user) {
            $recentProducts = VendorProduct::with(['product.division', 'product.category'])
                ->where('vendor_id', $user->id)->where('vendor_status',1)->where('approval_status',1)
                ->orderByDesc('created_at')
                ->take(8)
                ->get();
        }

        $today = Carbon::today();

        $advertisements = Advertisement::query()
            ->where('status', Advertisement::STATUS_ACTIVE)
            ->where('ad_position', 1)
            ->where(function ($query) use ($today) {
                $query->whereNull('validity_period_from')
                    ->orWhereDate('validity_period_from', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('validity_period_to')
                    ->orWhereDate('validity_period_to', '>=', $today);
            })
            ->orderByDesc('id')
            ->get();

        return view('vendor.vendor-dashboard', compact('user', 'recentProducts', 'advertisements'));
    }
}
