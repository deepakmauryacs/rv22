<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use App\Models\Category;
// use App\Models\Division;
// use App\Models\LiveVendorProduct;
use App\Models\RfqVendor;
use App\Models\Rfq;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\RfqVendorQuotation;
use Carbon\Carbon;
use DB;
use Auth;
use App\Traits\HasModulePermission;

use App\Exports\CisExport;
use Maatwebsite\Excel\Facades\Excel;



class CISController extends Controller
{
    use HasModulePermission;

    public function index(Request $request, $rfq_id)
    {
        $this->ensurePermission('ACTIVE_RFQS_CIS', 'view', '1');

        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }
        if ($rfq_data->buyer_rfq_status == 1) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS has not received any vendor quotes.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if (!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)) {
            return back()->with('error', 'No RFQ found');
        }

        $uom = getUOMList();

        $nature_of_business = DB::table("nature_of_business")
            ->select("id", "business_name")
            ->orderBy("id", "DESC")
            ->pluck("business_name", "id")->toArray();


        $cis = Rfq::rfqDetails($rfq_id);
        $rfq = $cis['rfq'];

        // echo "<pre>";
        // print_r($cis);
        // die;

        $filter['sort_price'] = request('sort_price');
        $filter['location'] = request('location');
        $filter['state_location'] = request('state_location');
        $filter['country_location'] = request('country_location');
        $filter['last_vendor'] = request('last_vendor');
        $filter['favourite_vendor'] = request('favourite_vendor');
        $filter['from_date'] = request('from_date');
        $filter['to_date'] = request('to_date');

        $is_date_filter = !empty($filter['from_date']) || !empty($filter['to_date']) ? true : false;

        $currencies = DB::table('currencies')->where('status', '1')->get();

        // ==== Prefill (Edit) data from new tables ====
        $auction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();

        $editId = $auction->id ?? null;

        // Selected vendors (prefer rfq_vendor_auctions; fallback to forward_auction_vendors)
        $selectedVendorIds = [];
        if ($editId) {
            $selectedVendorIds = DB::table('rfq_vendor_auctions')
                ->where('auction_id', $editId)
                ->pluck('vendor_id')
                ->map(fn($v) => (int)$v)
                ->toArray();

            if (empty($selectedVendorIds)) {
                $selectedVendorIds = DB::table('forward_auction_vendors')
                    ->where('auction_id', $editId)
                    ->pluck('vendor_id')
                    ->map(fn($v) => (int)$v)
                    ->toArray();
            }
        }

        // Variant start prices keyed by rfq_variant_id
        $prefillVariantPrices = [];
        if ($editId) {
            $prefillVariantPrices = DB::table('rfq_auction_variants')
                ->where('auction_id', $editId)
                ->pluck('start_price', 'rfq_variant_id') // [rfq_variant_id => start_price]
                ->toArray();
        }

        // echo "<pre>";
        // print_r($prefillVariantPrices); die();

        // Header fields (date/time/currency/decrement) for inputs
        $prefill = [];
        if ($auction) {
            // rfq_auctions.auction_date is stored as YYYY-MM-DD (varchar)
            try {
                $prefill['auction_date']      = \Carbon\Carbon::createFromFormat('Y-m-d', $auction->auction_date)->format('d/m/Y');
            } catch (\Throwable $e) {
                $prefill['auction_date']      = $auction->auction_date; // fallback
            }
            $prefill['auction_time']          = $auction->auction_start_time;  // HH:mm:ss
            $prefill['min_bid_currency']      = $auction->currency ?? 'INR';
            $prefill['min_bid_decrement']     = (float)$auction->min_bid_decrement;
            $prefill['auction_type']          = $auction->auction_type;
        }

        $liveAuction = DB::table('rfq_auctions')
            ->where('rfq_no', $rfq_id)
            ->orderByDesc('id')
            ->first();

        $current_status = null; // 1=Active, 2=Scheduled, 3=Closed
        if (!empty($liveAuction)) {
            // Use Asia/Kolkata to match your environment
            $today_date    = Carbon::today('Asia/Kolkata')->toDateString();   // 'Y-m-d'
            $current_time  = Carbon::now('Asia/Kolkata')->format('H:i:s');    // 'H:i:s'

            $auction_date       = $liveAuction->auction_date;                 // expect 'Y-m-d'
            $auction_start_time = $liveAuction->auction_start_time;           // 'H:i:s'
            $auction_end_time   = $liveAuction->auction_end_time;             // 'H:i:s'

            if ($auction_date == $today_date) {
                if ($current_time >= $auction_start_time && $current_time <= $auction_end_time) {
                    $current_status = 1; // Active
                } elseif ($current_time < $auction_start_time) {
                    $current_status = 2; // Scheduled
                } else {
                    $current_status = 3; // Closed
                }
            } elseif ($auction_date < $today_date) {
                $current_status = 3; // Closed
            } else {
                $current_status = 2; // Scheduled
            }
        }


        // log_peak_memory_usage();
        $data = compact(
            'uom',
            'cis',
            'rfq',
            'nature_of_business',
            'filter',
            'is_date_filter',
            'currencies',
            'auction',
            'editId',
            'selectedVendorIds',
            'prefill',
            'prefillVariantPrices',
            'current_status'
        );

        if ($request->input('export')) {
            return Excel::download(new CisExport($data), "CIS-Sheet-{$rfq_id}-" . now()->format('d-m-Y') . ".xlsx");
        }

        DB::table('rfqs')->where("rfq_id", $rfq_id)->update(['buyer_rfq_read_status' => 2]);

        return view('buyer.rfq.cis.rfq-cis', $data);
    }


    public function counter_offer($rfq_id)
    {
        $this->ensurePermission('COUNTER_OFFER_RFQ', 'view', '1');
        $parent_user_id = getParentUserId();
        $rfq_data = Rfq::where('record_type', 2)->where('rfq_id', $rfq_id)->where('buyer_id', $parent_user_id)->first();
        if (empty($rfq_data)) {
            return back()->with('error', 'RFQ not found.');
        }
        if ($rfq_data->buyer_rfq_status == 1) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS has not received any vendor quotes.');
        }

        $user_branch_id_only = getBuyerUserBranchIdOnly();
        if (!empty($user_branch_id_only) && !in_array($rfq_data->buyer_branch, $user_branch_id_only)) {
            return back()->with('error', 'No RFQ found');
        }

        $encoded = request('q');
        if (!$encoded) {
            session()->flash('error', "Missing encoded data");
            return redirect()->back();
        }
        // Decode Base64 string
        $decoded = base64_decode($encoded);
        if (!preg_match('/^\d+(,\d+)*$/', $decoded)) {
            session()->flash('error', "Decoded data format invalid");
            return redirect()->back();
        }

        $cis_vendors = explode(',', $decoded);

        $uom = getUOMList();

        $nature_of_business = DB::table("nature_of_business")
            ->select("id", "business_name")
            ->orderBy("id", "DESC")
            ->pluck("business_name", "id")->toArray();

        $cis = Rfq::rfqDetails($rfq_id, $cis_vendors);
        $rfq = $cis['rfq'];

        if ($rfq['is_auction'] == 1 || in_array($rfq['buyer_rfq_status'], [1, 5, 8, 10])) {
            return back()->with('error', 'RFQ ' . $rfq_id . ' CIS counter offer unable to open.');
        }

        $filter['sort_price'] = request('sort_price');
        $filter['location'] = request('location');
        $filter['state_location'] = request('state_location');
        $filter['country_location'] = request('country_location');
        $filter['last_vendor'] = request('last_vendor');
        $filter['favourite_vendor'] = request('favourite_vendor');
        $filter['from_date'] = request('from_date');
        $filter['to_date'] = request('to_date');

        $is_date_filter = !empty($filter['from_date']) || !empty($filter['to_date']) ? true : false;

        // if($is_date_filter==false && empty($cis['filter_vendors'])){
        //     $cis['filter_vendors'] = $cis_vendors;
        // }

        // echo "<pre>";
        // print_r($cis);
        // die;

        $currencies = DB::table('currencies')->where('status', '1')->get();

        return view('buyer.rfq.cis.counter-offer', compact('uom', 'cis', 'rfq', 'nature_of_business', 'filter', 'is_date_filter', 'currencies', 'encoded'));
    }
    public function save_counter_offer(Request $request, $rfq_id)
    {
        $this->ensurePermission('COUNTER_OFFER_RFQ', 'add', '1');
        if (
            isset($_POST['counter_offer'], $_POST['variant_vendors']) &&
            is_array($_POST['counter_offer']) &&
            is_array($_POST['variant_vendors'])
        ) {
            $counter_offer = $request->input('counter_offer');
            $variant_vendors = $request->input('variant_vendors');
        } else {
            // Handle invalid input, e.g. return error response or show message
            return response()->json(['status' => false, 'message' => 'Failed to update Counter offer price.']);
        }

        $buyer_id = getParentUserId();

        $rfq = DB::table('rfqs')->where('rfq_id', $rfq_id)->where('buyer_id', $buyer_id)->first();

        if (empty($rfq)) {
            return response()->json(['status' => false, 'message' => 'Something went wrong, please refresh the page']);
        }

        if (in_array($rfq->buyer_rfq_status, array(5, 8, 10))) {
            return response()->json(['status' => false, 'message' => 'RFQ already closed, cannot update counter offer price.']);
        }

        // echo "<pre>";
        // print_r($_POST);
        // die;

        DB::beginTransaction();

        try {

            $updated_vendors = [];
            foreach ($counter_offer as $variant_id => $buyer_price) {
                if (!isset($variant_vendors[$variant_id])) continue;

                foreach ($variant_vendors[$variant_id] as $vendor_id) {
                    // Get the last record id for vendor & variant
                    $latestId = DB::table('rfq_vendor_quotations')
                        ->where('rfq_id', $rfq_id)
                        ->where('vendor_id', $vendor_id)
                        ->where('rfq_product_variant_id', $variant_id)
                        ->orderBy('id', 'desc')
                        ->limit(1)
                        ->value('id');

                    if ($latestId) {
                        // Update buyer_price directly on the record by id
                        DB::table('rfq_vendor_quotations')
                            ->where('id', $latestId)
                            ->where('rfq_id', $rfq_id)
                            ->update([
                                'buyer_price' => $buyer_price,
                                'buyer_user_id' => Auth::user()->id,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                        $updated_vendors[$vendor_id] = true;
                    }
                }
            }
            $updated_vendors = array_keys($updated_vendors);
            if (!empty($updated_vendors)) {
                // Update vendor_status to 4 for this vendor in the relevant table
                DB::table('rfq_vendors')
                    ->whereIn('vendor_user_id', $updated_vendors)
                    ->where('rfq_id', $rfq_id)
                    ->update(['vendor_status' => 4]);

                if ($rfq->buyer_rfq_status != 9 && $rfq->buyer_rfq_status != 4) {
                    DB::table('rfqs')
                        ->where('buyer_id', $buyer_id)
                        ->where('rfq_id', $rfq_id)
                        ->update(['buyer_rfq_status' => 4]);
                }

                $rfq_vendors = RfqVendor::with(
                    'rfqVendorProfile:id,user_id,legal_name',
                    'rfqVendorDetails:id,name,email'
                )
                    ->whereIn('vendor_user_id', $updated_vendors)
                    ->where('rfq_id', $rfq_id)
                    ->groupBy('vendor_user_id')
                    ->select('vendor_user_id')
                    ->get();

                $vendor_details = [];

                $subject = "Counter Offer (RFQ No. " . $rfq_id . ")";

                $mail_data = vendorEmailTemplet('counter-offer-to-vendor');
                $admin_msg = $mail_data->mail_message;

                $admin_msg = str_replace('$rfq_date_formate', now()->format('d/m/Y'), $admin_msg);
                $admin_msg = str_replace('$rfq_number', $rfq_id, $admin_msg);
                $admin_msg = str_replace('$buyer_name', session('legal_name'), $admin_msg);
                $admin_msg = str_replace('$Accept_link', route("login"), $admin_msg);

                $mail_arr = array();
                foreach ($rfq_vendors as $vendor) {
                    // Make sure the relationships exist
                    if ($vendor->rfqVendorProfile && $vendor->rfqVendorDetails) {
                        $new_admin_msg = $admin_msg;
                        $new_admin_msg = str_replace('$vendor_name', $vendor->rfqVendorProfile->legal_name, $new_admin_msg);
                        $mail_arr[] = array('subject' => $subject, 'body' => $new_admin_msg, 'to' => $vendor->rfqVendorDetails->email);
                    }
                }
                sendMultipleDBEmails($mail_arr);

                $notification_data = array();
                $notification_data['rfq_no'] = $rfq_id;
                $notification_data['message_type'] = 'Counter Offer Received';
                $notification_data['notification_link'] = route("vendor.rfq.reply", ["rfq_id" => $rfq_id]); //route will change after RFQ Details page created on vendor side
                $notification_data['to_user_id'] = array_keys($updated_vendors);
                $status = sendNotifications($notification_data);
            }

            setSessionWithExpiry('counter_offer_rfq_number', $rfq_id);

            DB::commit();

            return response()->json([
                'status' => true,
                'redirect_url' => route("buyer.rfq.counter-offer-success", ['rfq_id' => $rfq_id]),
                'message' => 'Price updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle the error
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Price. ' . $e->getMessage(),
                'complete_message' => $e
            ]);
        }
    }
    public function counter_offer_success($rfq_id)
    {
        $this->ensurePermission('COUNTER_OFFER_RFQ', 'view', '1');
        $session_rfq_id = getSessionWithExpiry('counter_offer_rfq_number');
        if ($rfq_id != $session_rfq_id) {
            session()->flash('error', "Page has expired.");
            return redirect()->route('buyer.dashboard');
        }

        return view('buyer.rfq.cis.counter-offer-success', compact('rfq_id'));
    }
    public function quotation_received($rfq_id, $vendor_id)
    {
        $this->ensurePermission('COUNTER_OFFER_RFQ', 'view', '1');
        $company_id = getParentUserId();
        $rfq = Rfq::with([
            'rfqProducts',
            'rfqProducts.masterProduct:id,division_id,category_id,product_name',
            'rfqProducts.masterProduct.division:id,division_name',
            'rfqProducts.masterProduct.category:id,category_name',
            'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                $q->where('rfq_id', $rfq_id);
            },
            'rfqProducts.productVariants.vendorQuotations',
            'rfqVendors' => function ($q) use ($vendor_id) {
                $q->selectRaw('MAX(vendor_user_id) as vendor_user_id, MAX(rfq_id) as rfq_id');
                $q->where('vendor_user_id', $vendor_id);
            },
        ])
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $company_id)
            ->where('record_type', 2)
            ->first();
        if (empty($rfq)) {
            session()->flash('error', "RFQ not found");
            return redirect()->to(route('buyer.dashboard'));
        }

        $rfq_vendor = Vendor::where('user_id', $vendor_id)->first();

        $rfq_vendor_quotation = RfqVendorQuotation::with('vendor')->where('rfq_id', $rfq_id)->where('vendor_id', $vendor_id)->latest()->first();

        return view('buyer.rfq.cis.quotation-received', compact('rfq_id', 'vendor_id', 'rfq_vendor', 'rfq', 'rfq_vendor_quotation'));
    }

    public function quotation_received_print($rfq_id, $vendor_id)
    {
        $this->ensurePermission('COUNTER_OFFER_RFQ', 'view', '1');
        $company_id = getParentUserId();
        $rfq = Rfq::with([
            'rfqProducts',
            'rfqProducts.masterProduct:id,division_id,category_id,product_name',
            'rfqProducts.masterProduct.division:id,division_name',
            'rfqProducts.masterProduct.category:id,category_name',
            'rfqProducts.productVariants' => function ($q) use ($rfq_id) {
                $q->where('rfq_id', $rfq_id);
            },
            'rfqProducts.productVariants.vendorQuotations',
            'getLastRfqVendorQuotation',
            'rfqVendors' => function ($q) use ($vendor_id) {
                $q->selectRaw('MAX(vendor_user_id) as vendor_user_id, MAX(rfq_id) as rfq_id');
                $q->where('vendor_user_id', $vendor_id);
            },
        ])
            ->where('rfq_id', $rfq_id)
            ->where('buyer_id', $company_id)
            ->where('record_type', 2)
            ->first();
        if (empty($rfq)) {
            session()->flash('error', "RFQ not found");
            return redirect()->to(route('buyer.dashboard'));
        }

        $rfq_vendor = Vendor::where('user_id', $vendor_id)->first();
        $rfq_vendor_quotation = RfqVendorQuotation::with('vendor')->where('rfq_id', $rfq_id)->where('vendor_id', $vendor_id)->latest()->first();
        return view('buyer.rfq.cis.received-quotation-pdf', compact('rfq_id', 'vendor_id', 'rfq_vendor', 'rfq', 'rfq_vendor_quotation'));
    }

    public function last_cis_po(Request $request)
    {
        $this->ensurePermission('ACTIVE_RFQS_CIS', 'view', '1');
        $rfq_id=$request->rfq_id;
        $cis_po=$request->cis_po;
        $product_id=$request->product_id;
        $html='';
        if($cis_po=='cis')
        {
            $rfq = Rfq::select('id','rfq_id','scheduled_date','created_at')->where('buyer_id', getParentUserId())
                    ->where('rfq_id','!=', $rfq_id)
                    ->whereHas('rfqProducts', function ($q) use ($product_id) {
                        $q->where('product_id', $product_id);
                    })->where('record_type',2)->whereNotIn('buyer_rfq_status',[1,2])
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
            if($rfq->isNotEmpty()){
                foreach($rfq as $key => $value){
                    $html.='<tr><td>'.(++$key).'</td><td>'.$value->rfq_id.'</td><td>'.date('d/m/Y', strtotime((!empty($value->scheduled_date)?$value->scheduled_date:$value->created_at))).'</td><td><a target="_blank" href="'.route('buyer.rfq.cis-sheet', $value->rfq_id).'">Click to View</a></td></tr>';
                }
            }else{
                $html='<tr><td colspan="4">Last CIS not found for selected product</td></tr>';
            }
        }
        if($cis_po=='po')
        {
            $query = Order::select('id','po_number','created_at')->with('order_variants')->where('buyer_id', getParentUserId())->where('order_status','!=','3');
            $query->whereHas('order_variants', function ($q) use ($product_id) {
                $q->where('product_id', $product_id);
            })->orderBy('created_at', 'desc')->limit(3);
            $rfq = $query->get();
            if($rfq->isNotEmpty()){
                foreach($rfq as $key => $value){
                    $html.='<tr><td>'.(++$key).'</td><td>'.$value->po_number.'</td><td>'.date('d/m/Y', strtotime($value->created_at)).'</td><td><a target="_blank" href="'.route('buyer.rfq.order-confirmed.view', $value->id).'">Click to View</a></td></tr>';
                }
            }else{
                $html='<tr><td colspan="4">Last PO not found for selected product</td></tr>';
            }
        }
        return response()->json($html);
    }
}
