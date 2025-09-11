<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Traits\HasModulePermission;
class ProductsCategoryDivisionReportController extends Controller
{
    use HasModulePermission;
    public function index(Request $request) {

        $this->ensurePermission('DIVISION_AND_CATEGORY_WISE');

        $query = $this->querires($request);
        $results = $query->orderBy('product_name')->paginate(25);
        if ($request->ajax()) {
            return view('admin.reports.partials.product-division-category-table', compact('results'))->render();
        }
        return view('admin.reports.product-division-category',compact('results'));
    }


    public function exportTotal(Request $request){
        $query = $this->querires($request);
        $total = $query->count();
        return response()->json(['total' => $total]);
    }

    public function exportBatch(Request $request){
        $limit = intval($request->input('limit'));
        $lastId = $request->input('last_id');

        $query = $this->querires($request)->orderBy('id');
        if ($lastId !== null) {
            $query->where('id', '>', $lastId);
        }

        $results = $query->take($limit)->get();

        $result = [];
        foreach ($results as $res) {
            $result[] = [
                $res->product_name ?? '',
                $res->division->division_name ?? '',
                $res->category->category_name ?? '',
                optional($res->master_alias)->pluck('alias')->implode(', '),
                optional($res->vendor_alias)->pluck('alias')->implode(', '),
                $res->vendor_count,
                $res->created_at->format('d/m/Y'),
                $res->rfq_count,
                $res->order_count,
                $res->status == '1' ? 'Active' : 'Inactive',
            ];
        }

        $lastRow = $results->last();

        return response()->json([
            'data' => $result,
            'last_id' => $lastRow->id ?? null,
        ]);
    }

    private function querires(Request $request)
    {
        $rfqSub = DB::table('rfq_products')
            ->select('product_id', DB::raw('COUNT(*) as rfq_count'))
            ->groupBy('product_id');

        $orderSub = DB::table('order_variants')
            ->join('orders', function ($join) {
                $join->on('orders.id', '=', 'order_variants.order_id')
                    ->where('orders.order_status', 1);
            })
            ->select('order_variants.product_id', DB::raw('COUNT(*) as order_count'))
            ->groupBy('order_variants.product_id');

        $vendorSub = DB::table('product_vendors')
            ->select('product_id', DB::raw('COUNT(*) as vendor_count'))
            ->groupBy('product_id');

        $query = Product::query()
            ->select(
                'products.id',
                'products.division_id',
                'products.category_id',
                'products.product_name',
                'products.status',
                'products.created_at'
            )
            ->with(['division', 'category', 'master_alias', 'vendor_alias'])
            ->leftJoinSub($rfqSub, 'rp', function ($join) {
                $join->on('rp.product_id', '=', 'products.id');
            })
            ->leftJoinSub($orderSub, 'ov', function ($join) {
                $join->on('ov.product_id', '=', 'products.id');
            })
            ->leftJoinSub($vendorSub, 'pv', function ($join) {
                $join->on('pv.product_id', '=', 'products.id');
            })
            ->addSelect('rp.rfq_count', 'ov.order_count', 'pv.vendor_count')
            ->groupBy(
                'products.id',
                'products.division_id',
                'products.category_id',
                'products.product_name',
                'products.status',
                'products.created_at'
            );

        if ($request->filled('product_name')) {
            $query->where('products.product_name', 'like', '%' . $request->input('product_name') . '%');
        }
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('products.created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('products.created_at', '>=', $request->input('from_date'));
        } elseif ($request->filled('to_date')) {
            $query->whereDate('products.created_at', '<=', $request->input('to_date'));
        }
        if ($request->filled('status')) {
            $query->where('products.status', $request->input('status'));
        }
        return $query;
    }
}
