<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rfq;
use DB;

class RFQUnapprovedOrderController extends Controller
{
    public function index()
    {
        return view('buyer.unapproved-orders.index');
    }
    public function create($rfq_id)
    {
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if(empty($rfq_data)){
            return back()->with('error', 'RFQ not found.');
        }

        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();
        if(!empty($auction)){
            $auction_status = getAuctionStatus($auction->auction_date, $auction->auction_start_time, $auction->auction_end_time);
            if($auction_status == 1){
                return back()->with('error', 'The auction for RFQ '.$rfq_id.' has been created and is still in scheduled/progress.');
            }
        }
        unset($auction);
        if(in_array($rfq_data->buyer_rfq_status, [1, 5, 8, 10])){
            return back()->with('error', 'RFQ '.$rfq_id.' Unapproved Order is unable to open.');
        }

        $encoded = request('q');
        if (!$encoded) {
            session()->flash('error', "Missing encoded data");
            return redirect()->back();
        }
        // Decode Base64 string
        $decoded = base64_decode($encoded);
        if (!preg_match('/^(\d+-\d+,)*(\d+-\d+)$/', $decoded)) {
            session()->flash('error', "Decoded data format invalid");
            return redirect()->back();
        }

        $vendors = explode(',', $decoded);
        $vendor_variants = [];
        $vendor_ids = [];
        $variants = [];

        foreach ($vendors as $vendor) {
            list($vendorId, $variantId) = explode('-', $vendor);

            if (!isset($vendor_variants[$vendorId])) {
                $vendor_variants[$vendorId] = [];
            }
            $vendor_variants[$vendorId][] = $variantId;
            $vendor_ids[] = $vendorId;
            $variants[] = $variantId;
        }

        $vendor_ids = array_unique($vendor_ids);
        $variants = array_unique($variants);

        $vendor_data = [
            'vendor_variants' => $vendor_variants,
            'vendors' => $vendor_ids,
            'variants' => $variants
        ];

        $unapprovedOrder = Rfq::unapprovedOrder($rfq_id, $vendor_data);

        if(isset($unapprovedOrder['all_qty_over']) && $unapprovedOrder['all_qty_over'] == true) {
            session()->flash('error', "Please check unapproved order as all balance qty of this RFQ has moved there.");
            return redirect()->to(route('buyer.dashboard'));
        }

        $uom = getUOMList();

        $taxes = DB::table("taxes")
                    ->select("id", "tax")
                    ->pluck("tax", "id")->toArray();

        return view('buyer.unapproved-orders.create', compact('uom', 'taxes', 'unapprovedOrder'));
    }
}
