<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\HasModulePermission;

class AuctionRFQSummaryReportController extends Controller
{
    use HasModulePermission;

    private function baseQuery(Request $request)
    {
        $query = DB::table('rfq_auctions as ra')
            ->leftJoin('rfq_vendor_auctions as rva', 'ra.id', '=', 'rva.auction_id')
            ->leftJoin('vendors as v', 'rva.vendor_id', '=', 'v.user_id')
            ->leftJoin('users as u', 'v.user_id', '=', 'u.id')
            ->leftJoin('rfq_auction_variants as rav', 'ra.id', '=', 'rav.auction_id')
            ->leftJoin('products as p', 'rav.product_id', '=', 'p.id')
            ->leftJoin('buyers as b', 'ra.buyer_id', '=', 'b.user_id');

        if ($request->filled('buyer_name')) {
            $query->where('b.legal_name', 'like', '%' . $request->buyer_name . '%');
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('ra.auction_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('ra.auction_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('ra.auction_date', '<=', $request->to_date);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $this->ensurePermission('VENDOR_REPORTS');

        $query = $this->baseQuery($request)->select(
            'ra.rfq_no',
            'ra.auction_date',
            'ra.auction_start_time',
            'ra.auction_end_time',
            'b.legal_name as buyer_legal_name',
            'p.product_name',
            'v.legal_name as vendor_legal_name',
            'u.email',
            'u.mobile',
            'u.status as vendor_user_status'
        );

        $perPage = $request->input('per_page', 25);
        $results = $query->orderBy('ra.auction_date', 'desc')
            ->paginate($perPage)
            ->appends($request->all());

        if ($request->ajax()) {
            return view('admin.reports.partials.auction-rfq-summary-report-table', compact('results'))
                ->render();
        }

        return view('admin.reports.auction-rfq-summary-report', compact('results'));
    }

    public function exportTotal(Request $request)
    {
        $total = DB::query()->fromSub(
            $this->baseQuery($request)->select('ra.id', 'rva.vendor_id'),
            'ra_summary'
        )->count();

        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request)
    {
        $limit = intval($request->input('limit'));
        $lastRfqId = $request->input('last_rfq_id');
        $lastVendorId = $request->input('last_vendor_id');

        $query = $this->baseQuery($request)->select(
            'ra.id as ra_id',
            'rva.vendor_id',
            'ra.rfq_no',
            'ra.auction_date',
            'ra.auction_start_time',
            'ra.auction_end_time',
            'b.legal_name as buyer_legal_name',
            'p.product_name',
            'v.legal_name as vendor_legal_name',
            'u.email',
            'u.mobile',
            'u.status as vendor_user_status'
        )->orderBy('ra.id')->orderBy('rva.vendor_id');

        if ($lastRfqId !== null && $lastVendorId !== null) {
            $query->where(function ($q) use ($lastRfqId, $lastVendorId) {
                $q->where('ra.id', '>', $lastRfqId)
                    ->orWhere(function ($sub) use ($lastRfqId, $lastVendorId) {
                        $sub->where('ra.id', $lastRfqId)
                            ->where('rva.vendor_id', '>', $lastVendorId);
                    });
            });
        }

        $dataList = $query->take($limit)->get();

        $result = [];
        foreach ($dataList as $value) {
            $result[] = [
                $value->rfq_no ?? '',
                date('d/m/Y', strtotime($value->auction_date)) ?? '',
                date('h:i A', strtotime($value->auction_start_time)) . ' To ' .
                    date('h:i A', strtotime($value->auction_end_time)),
                $value->buyer_legal_name ?? '',
                $value->product_name ?? '',
                $value->vendor_legal_name ?? '',
                $value->email ?? '',
                $value->mobile ?? '',
                $value->vendor_user_status == 1
                    ? 'Active'
                    : ($value->vendor_user_status == 0 ? 'Inactive' : ''),
                '',
                '',
            ];
        }

        $lastRow = $dataList->last();

        return response()->json([
            'data' => $result,
            'last_rfq_id' => $lastRow->ra_id ?? null,
            'last_vendor_id' => $lastRow->vendor_id ?? null,
        ]);
    }
}
