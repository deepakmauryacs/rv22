<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForwardAuctionController extends Controller
{
    public function __construct()
    {
        // Set the timezone for all functions in this controller
        date_default_timezone_set('Asia/Kolkata'); // Change as per your requirement
        // Optionally, you can set Carbon's default timezone too
        Carbon::setLocale('en');
        // You can add more initialization logic here
    }

    public function index(Request $request)
    {
        $vendorId = getParentUserId();

        $query = DB::table('forward_auctions as fa')
            ->selectRaw('
                fa.auction_id,
                GROUP_CONCAT(fap.product_name SEPARATOR ", ") as products,
                MAX(bd.legal_name) as buyer_name,
                MAX(u.name) as buyer_user_name,
                fa.schedule_date,
                fa.schedule_start_time,
                fa.schedule_end_time
            ')
            ->Join('forward_auction_products as fap', 'fa.auction_id', '=', 'fap.auction_id')
            ->Join('forward_auction_vendors as fav', 'fav.auction_product_id', '=', 'fap.id')
            ->Join('buyers as bd', 'fa.buyer_id', '=', 'bd.user_id')
            ->Join('users as u', 'fa.buyer_user_id', '=', 'u.id')
            ->where('fav.vendor_id', $vendorId);

        // Filtering
        if ($request->filled('auction_no')) {
            $query->where('fa.auction_id', $request->auction_no);
        }
        if ($request->filled('buyer_name')) {
            $query->where('bd.legal_name', 'like', '%' . $request->buyer_name . '%');
        }
        if ($request->filled('auction_date')) {
            $date = Carbon::createFromFormat('d/m/Y', $request->auction_date)->format('Y-m-d');
            $query->where('fa.schedule_date', $date);
        }

        $query->groupBy('fa.auction_id', 'fa.schedule_date', 'fa.schedule_start_time', 'fa.schedule_end_time');
        $query->orderByDesc('fa.schedule_date')->orderByDesc('fa.schedule_start_time');

        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('vendor.forward-auction.partials.table', compact('results'))->render();
        }

        return view('vendor.forward-auction.index', compact('results'));
    }

    public function auctionReply($auction_id)
    {
        // Use a repository/service, or just call a model method directly
        $auction = $this->getAuctionDetails($auction_id);

        // Use Laravel Auth for vendor ID (adjust if using guards)
        $vendorId = getParentUserId(); // or session('system_admin.id') if you have custom session
        $currencies = DB::table('currencies')->where('status', '1')->get();

        return view('vendor.forward-auction.auction-reply', [
            'page_title' => 'Forward Auction Reply',
            'page_heading' => 'Forward Auction Reply',
            'auction' => $auction,
            'auction_id' => $auction_id,
            'vendor_id' => $vendorId,
            'currencies' => $currencies,
        ]);
    }

    // Use this in a Model, Repository, or even Controller as needed.
    public function getAuctionDetails($auction_id)
    {
        return \DB::table('forward_auctions as fa')
            ->select([
                'fa.auction_id as auction_id',
                'fa.buyer_id',
                'fa.buyer_user_id',
                'b.legal_name as buyer_name',
                'u.name as username',
                'fa.branch_address as branch_address',
                'fa.remarks as remarks',
                'fa.price_basis as price_basis',
                'fa.payment_terms as payment_terms',
                'fa.delivery_period as delivery_period',
                'fa.schedule_date',
                'fa.schedule_start_time',
                'fa.schedule_end_time',
                'fa.created_at',
                'fa.currency',
                'branch.name as branch_name'
            ])
            ->leftJoin('buyers as b', 'fa.buyer_id', '=', 'b.user_id')
            ->leftJoin('users as u', 'fa.buyer_user_id', '=', 'u.id')
            ->join('branch_details as branch', 'branch.branch_id', '=', 'fa.buyer_branch')
            ->where('fa.auction_id', $auction_id)
            ->first(); 
    }


    public function submitForwardReply(Request $request)
    {
        $auction_id    = $request->post('auction_id');
        $vendor_id     = $request->post('vendor_id');
        $buyer_id      = $request->post('buyer_id');
        $buyer_user_id = $request->post('buyer_user_id');
        $bid_prices    = $request->post('bid_price');

        // Validate input
        if (!$this->isValidBidPrices($bid_prices)) {
            return response()->json(['success' => false, 'message' => 'No valid prices received.']);
        }

        $auction = $this->getAuction($auction_id);
        if (!$auction) {
            return response()->json(['success' => false, 'message' => 'Auction not found.']);
        }
        if ($this->isAuctionForceStopped($auction)) {
            return response()->json(['success' => false, 'hasAuctionEnded' => true, 'message' => 'Auction has ended.']);
        }
        if (!$this->isAuctionLive($auction)) {
            return response()->json(['success' => false, 'message' => 'Auction is not live.']);
        }

        $extend_time = $this->shouldExtendTime($auction);

        foreach ($bid_prices as $product_id => $price) {
            $validateResult = $this->validateBid($auction, $product_id, $vendor_id, $price);
            if ($validateResult !== true) {
                return $validateResult; // Already json response
            }
            $this->upsertBid($auction, $product_id, $vendor_id, $buyer_id, $buyer_user_id, $price);
        }

        if ($extend_time) {
            $this->maybeExtendAuctionTime($auction_id, $bid_prices, $vendor_id, $auction);
        }

        return response()->json(['success' => true, 'message' => 'Bid submitted successfully!']);
    }

    // ------------ MICRO FUNCTIONS BELOW ---------------

    protected function isValidBidPrices($bid_prices)
    {
        return ($bid_prices && is_array($bid_prices));
    }

    protected function getAuction($auction_id)
    {
        return DB::table('forward_auctions')->where('auction_id', $auction_id)->first();
    }

    protected function isAuctionForceStopped($auction)
    {
        return $auction->is_forcestop == '1';
    }

    protected function isAuctionLive($auction)
    {
        $start_time = strtotime($auction->schedule_date . ' ' . $auction->schedule_start_time);
        $end_time = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);
        $current_time = time();

        if ($current_time < $start_time) return false;
        if ($current_time > $end_time) return false;
        return true;
    }

    protected function shouldExtendTime($auction)
    {
        $end_time = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);
        $current_time = time();
        return ($end_time - $current_time <= 120);
    }

    protected function validateBid($auction, $product_id, $vendor_id, $price)
    {
        if (!is_numeric($price) || $price <= 0) {
            return response()->json(['success' => false, 'message' => "Invalid price for product $product_id."]);
        }

        $product = DB::table('forward_auction_products')
            ->where('auction_id', $auction->auction_id)
            ->where('id', $product_id)
            ->first();

        if (!$product) {
            return response()->json(['success' => false, 'message' => "Invalid product ID: $product_id"]);
        }

        $start_price   = (float)$product->start_price;
        $min_increment = (float)$product->min_bid_increment_amount;

        $previous = DB::table('forward_auction_replies')
            ->where([
                'auction_id'         => $auction->auction_id,
                'auction_product_id' => $product_id,
                'vendor_id'          => $vendor_id
            ])->first();

        $prev_price = $previous ? (float)$previous->price : null;

        $l1_price = DB::table('forward_auction_replies')
            ->where('auction_id', $auction->auction_id)
            ->where('auction_product_id', $product_id)
            ->max('price');
        $l1_price = $l1_price ? (float)$l1_price : 0;

        // Main bid validation logic
        if ($l1_price > 0 && (!$previous || $price > $prev_price)) {
            $min_next = $l1_price + $min_increment;
            if ($price < $min_next) {
                return response()->json([
                    'success' => false,
                    'status'  => 2,
                    'message' => "Bid (₹" . number_format($price, 2) . ") must be at least ₹" . number_format($min_next, 2) . " (H1: ₹" . number_format($l1_price, 2) . " + ₹" . number_format($min_increment, 2) . " increment)"
                ]);
            }
        } else {
            if (!$previous && $price < $start_price) {
                return response()->json([
                    'success' => false,
                    'status'  => 1,
                    'message' => "First bid (₹" . number_format($price, 2) . ") cannot be less than Start Price (₹" . number_format($start_price, 2) . ")"
                ]);
            }
            if ($previous && $price != $prev_price) {
                $min_next = $prev_price + $min_increment;
                return response()->json([
                    'success' => false,
                    'status'  => 1,
                    'message' => "New bid (₹" . number_format($price, 2) . ") must be at least ₹" . number_format($min_next, 2) . " (Prev: ₹" . number_format($prev_price, 2) . " + ₹" . number_format($min_increment, 2) . " increment)"
                ]);
            }
        }

        return true; // validation success
    }

    protected function upsertBid($auction, $product_id, $vendor_id, $buyer_id, $buyer_user_id, $price)
    {
        $data = [
            'auction_id'         => $auction->auction_id,
            'auction_product_id' => $product_id,
            'vendor_id'          => $vendor_id,
            'buyer_id'           => $buyer_id,
            'buyer_user_id'      => $buyer_user_id,
            'price'              => $price,
            'updated_at'         => now(),
            'created_at'         => now(),
        ];

        $exists = DB::table('forward_auction_replies')
            ->where([
                'auction_id'         => $auction->auction_id,
                'auction_product_id' => $product_id,
                'vendor_id'          => $vendor_id,
            ])->first();

        if ($exists) {
            DB::table('forward_auction_replies')->where('id', $exists->id)->update($data);
        } else {
            DB::table('forward_auction_replies')->insert($data);
        }
    }

    protected function maybeExtendAuctionTime($auction_id, $bid_prices, $vendor_id, $auction)
    {
        foreach ($bid_prices as $product_id => $price) {
            $top_vendors = DB::table('forward_auction_replies')
                ->select('vendor_id', 'price')
                ->where('auction_product_id', $product_id)
                ->orderByDesc('price')
                ->limit(3)
                ->get();

            foreach ($top_vendors as $index => $vendor) {
                if ($vendor->vendor_id == $vendor_id && $index + 1 <= 3) {
                    $end_time = strtotime($auction->schedule_date . ' ' . $auction->schedule_end_time);
                    $new_end_time = Carbon::createFromTimestamp($end_time)->addSeconds(120)->format('H:i:s');
                    DB::table('forward_auctions')->where('auction_id', $auction_id)
                        ->update(['schedule_end_time' => $new_end_time]);
                    break 2;
                }
            }
        }
    }

     // ------------ MICRO FUNCTIONS BELOW END---------------


    public function getLiveRanks(Request $request)
    {
        $auction_id = $request->post('auction_id');
        $vendor_id = $request->post('vendor_id');

        if (!$auction_id || !$vendor_id) {
            return response()->json(['status' => false, 'message' => 'Invalid request']);
        }

        $auction = DB::table('forward_auctions')
            ->select('is_forcestop')
            ->where('auction_id', $auction_id)
            ->first();

        if (!$auction) {
            return response()->json(['status' => false, 'message' => 'Auction not found']);
        }

        // Raw SQL to get highest price per vendor per product
        $sql = "
            SELECT vendor_id, auction_product_id, price
            FROM (
                SELECT vendor_id, auction_product_id, MAX(price) as price
                FROM forward_auction_replies
                WHERE auction_id = ?
                GROUP BY vendor_id, auction_product_id
            ) as max_prices
            ORDER BY auction_product_id DESC, price DESC";

        $result = DB::select($sql, [$auction_id]);

        // Build rankings grouped by product
        $rankings = [];
        foreach ($result as $row) {
            $pid = $row->auction_product_id;
            if (!isset($rankings[$pid])) {
                $rankings[$pid] = [];
            }
            $rankings[$pid][] = [
                'vendor_id' => $row->vendor_id,
                'price' => (float) $row->price
            ];
        }

        // Assign ranks and always include L1 price
        $responseRanks = [];
        foreach ($rankings as $product_id => $vendorList) {
            $rank = 1;
            $last_price = null;
            $l1_price = !empty($vendorList) ? $vendorList[0]['price'] : 0.00;
            $found_vendor = false;

            foreach ($vendorList as $index => $data) {
                if ($last_price !== null && $data['price'] < $last_price) {
                    $rank = $index + 1;
                }
                if ($data['vendor_id'] == $vendor_id) {
                    $responseRanks[$product_id] = [
                        'rank' => $rank,
                        'l1_price' => $l1_price
                    ];
                    $found_vendor = true;
                    break;
                }
                $last_price = $data['price'];
            }
            if (!$found_vendor) {
                $responseRanks[$product_id] = [
                    'rank' => null,
                    'l1_price' => $l1_price
                ];
            }
        }

        return response()->json([
            'status' => true,
            'is_forcestop' => $auction->is_forcestop,
            'ranks' => $responseRanks,
        ]);
    }

    public function checkBidRank(Request $request)
    {
        $product_id = $request->post('product_id');
        $bid_price = $request->post('bid_price');
        $vendor_id = auth()->user()->id;

        $rank_1_data = DB::table('forward_auction_replies as far')
            ->select('far.price', 'far.vendor_id')
            ->where('far.auction_product_id', $product_id)
            ->orderByDesc('far.price')
            ->first();

        if ($rank_1_data) {
            $rank_1_price = round((float)$rank_1_data->price, 2);
            $rank_1_vendor = $rank_1_data->vendor_id;

            // Check if price matches rank #1 price from another vendor
            if ($rank_1_price == round($bid_price, 2) && $rank_1_vendor != $vendor_id) {
                return response()->json([
                    'success' => false,
                    'status' => 3,
                    'message' => 'This price ₹' . number_format($bid_price, 2) . ' has already been submitted by another vendor. You will need to submit a higher rate.',
                    'is_duplicate' => true,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'status' => 1,
                    'message' => '',
                    'is_duplicate' => false,
                ]);
            }
        } else {
            return response()->json([
                'success' => true,
                'status' => 1,
                'message' => '',
                'is_duplicate' => false,
            ]);
        }
    }
}
