<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\HasModulePermission;
class ProductsCategoryDivisionReportController extends Controller
{
    use HasModulePermission;
    public function index(Request $request) {

        $this->ensurePermission('DIVISION_AND_CATEGORY_WISE');

        $query = $this->querires($request);
        $results = $query->orderBy('products.product_name')->paginate(25);
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

        $query = $this->querires($request)->orderBy('products.id');
        if ($lastId !== null) {
            $query->where('products.id', '>', $lastId);
        }

        $results = $query->take($limit)->get();

        $result = [];
        foreach ($results as $res) {
            $result[] = [
                $res->product_name ?? '',
                $res->division_name ?? '',
                $res->category_name ?? '',
                $res->master_alias ?? '',
                $res->vendor_alias ?? '',
                $res->vendor_count,
                Carbon::parse($res->created_at)->format('d/m/Y'),
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
        $query = DB::table('products')
            ->select(
                'products.id',
                'products.division_id',
                'products.category_id',
                'products.product_name',
                'products.status',
                'products.created_at',
                'divisions.division_name',
                'categories.category_name',
                DB::raw('COALESCE(ma.aliases, "") as master_alias'),
                DB::raw('COALESCE(va.aliases, "") as vendor_alias'),
                DB::raw('COALESCE(rp.rfq_count,0) as rfq_count'),
                DB::raw('COALESCE(ov.order_count,0) as order_count'),
                DB::raw('COALESCE(vp.vendor_count,0) as vendor_count')
            )
            ->leftJoin('divisions', 'divisions.id', '=', 'products.division_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoinSub(
                'SELECT product_id, GROUP_CONCAT(alias ORDER BY alias SEPARATOR ", ") as aliases
                 FROM product_alias WHERE alias_of = 1 GROUP BY product_id',
                'ma',
                'ma.product_id',
                '=',
                'products.id'
            )
            ->leftJoinSub(
                'SELECT product_id, GROUP_CONCAT(alias ORDER BY alias SEPARATOR ", ") as aliases
                 FROM product_alias WHERE alias_of = 2 GROUP BY product_id',
                'va',
                'va.product_id',
                '=',
                'products.id'
            )
            ->leftJoinSub(
                'SELECT product_id, COUNT(*) as rfq_count FROM rfq_products GROUP BY product_id',
                'rp',
                'rp.product_id',
                '=',
                'products.id'
            )
            ->leftJoinSub(
                'SELECT ov.product_id, COUNT(*) as order_count FROM order_variants ov INNER JOIN orders o ON o.po_number = ov.po_number AND o.order_status = 1 GROUP BY ov.product_id',
                'ov',
                'ov.product_id',
                '=',
                'products.id'
            )
            ->leftJoinSub(
                'SELECT product_id, COUNT(*) as vendor_count FROM vendor_products WHERE vendor_status = 1 AND approval_status = 1 GROUP BY product_id',
                'vp',
                'vp.product_id',
                '=',
                'products.id'
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
