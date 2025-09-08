<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\HasModulePermission;

class ForwardAuctionSummaryReportController extends Controller
{
    use HasModulePermission;

    protected function baseQuery(Request $request)
    {
        $query = DB::table('forward_auctions as fa')
            ->selectRaw('fa.id as fa_id, fa.auction_id, fav.vendor_id, GROUP_CONCAT(DISTINCT fap.product_name SEPARATOR ", ") as products, b.legal_name as buyer_name, v.legal_name as vendor_name, fa.schedule_date, fa.schedule_start_time, fa.schedule_end_time, CASE WHEN COUNT(far.id) > 0 THEN "Yes" ELSE "No" END as participated')
            ->join('forward_auction_products as fap', 'fa.auction_id', '=', 'fap.auction_id')
            ->join('forward_auction_vendors as fav', 'fav.auction_product_id', '=', 'fap.id')
            ->join('vendors as v', 'fav.vendor_id', '=', 'v.user_id')
            ->join('buyers as b', 'fa.buyer_id', '=', 'b.user_id')
            ->leftJoin('forward_auction_replies as far', function ($join) {
                $join->on('far.auction_id', '=', 'fa.auction_id')
                     ->on('far.vendor_id', '=', 'fav.vendor_id');
            });

        if ($request->filled('auction_id')) {
            $query->where('fa.auction_id', 'like', '%' . $request->auction_id . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('v.legal_name', 'like', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('buyer_name')) {
            $query->where('b.legal_name', 'like', '%' . $request->buyer_name . '%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('fa.schedule_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('fa.schedule_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('fa.schedule_date', '<=', $request->to_date);
        }

        $query->groupBy('fa.id', 'fa.auction_id', 'b.legal_name', 'v.legal_name', 'fa.schedule_date', 'fa.schedule_start_time', 'fa.schedule_end_time', 'fav.vendor_id');

        return $query;
    }

    public function index(Request $request)
    {
        $this->ensurePermission('VENDOR_REPORTS');

        $query = $this->baseQuery($request)
            ->orderByDesc('fa.schedule_date')
            ->orderByDesc('fa.schedule_start_time');
        $perPage = $request->input('per_page', 25);
        $results = $query->paginate($perPage)->appends($request->all());

        if ($request->ajax()) {
            return view('admin.forward-auction-report.partials.forward-auction-summary-report-table', compact('results'))->render();
        }

        return view('admin.forward-auction-report.forward-auction-summary-report', compact('results'));
    }

    public function exportTotal(Request $request)
    {
        $total = DB::query()->fromSub($this->baseQuery($request), 'fa_summary')->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request)
    {
        $limit = intval($request->input('limit'));
        $lastFaId = $request->input('last_auction_id');
        $lastVendorId = $request->input('last_vendor_id');

        $query = $this->baseQuery($request)->orderBy('fa.id')->orderBy('fav.vendor_id');

        if ($lastFaId !== null && $lastVendorId !== null) {
            $query->where(function ($q) use ($lastFaId, $lastVendorId) {
                $q->where('fa.id', '>', $lastFaId)
                  ->orWhere(function ($sub) use ($lastFaId, $lastVendorId) {
                      $sub->where('fa.id', $lastFaId)
                          ->where('fav.vendor_id', '>', $lastVendorId);
                  });
            });
        }

        $data_list = $query->take($limit)->get();

        $result = [];
        foreach ($data_list as $row) {
            $result[] = [
                $row->auction_id,
                $row->products,
                $row->buyer_name,
                $row->vendor_name,
                date('d/m/Y', strtotime($row->schedule_date)) . ' ' . date('h:i A', strtotime($row->schedule_start_time)) . ' To ' . date('h:i A', strtotime($row->schedule_end_time)),
                $row->participated,
            ];
        }

        $lastRow = $data_list->last();
        return response()->json([
            'data' => $result,
            'last_auction_id' => $lastRow->fa_id ?? null,
            'last_vendor_id' => $lastRow->vendor_id ?? null,
        ]);
    }
}

