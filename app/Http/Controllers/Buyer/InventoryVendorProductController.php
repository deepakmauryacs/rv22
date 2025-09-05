<?php
namespace App\Http\Controllers\Buyer;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Product;

class InventoryVendorProductController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query');
        $products = Product::query()
            ->select('id', 'product_name', 'category_id', 'division_id')
            ->with([
                'category:id,category_name',
                'division:id,division_name',
            ])
            ->where(function ($q) use ($query) {
                $q->where('product_name', 'LIKE', "%{$query}%")
                ->orWhereHas('product_aliases', function ($aliasQuery) use ($query) {
                    $aliasQuery->where('alias', 'LIKE', "%{$query}%");
                });
            })
            ->orderBy('product_name', 'asc')
            ->limit(100)
            ->get();
        $formatted = $products->map(function ($product) {
            return [
                'id'            => $product->id,
                'product_name'  => $product->product_name,
                'category_id'   => $product->category_id,
                'division_id'   => $product->division_id,
                'category_name' => optional($product->category)->category_name,
                'division_name' => optional($product->division)->division_name,
            ];
        });

        return response()->json($formatted);
    }

    public function searchAllProduct(Request $request)
    {
        $searchData = $request->input('search_data');
        $searchType = $request->input('search_type');
        $pageNo     = $request->input('page_no', 1);

        // Validate input
        if (empty(trim($searchData))) {
            return response()->json([
                'status'  => 'fail',
                'message' => 'Empty search data',
            ]);
        }

        // Fetch matching products with relationships
        $products = Product::with(['category', 'division'])
            ->where(function ($query) use ($searchData) {
                $query->where('product_name', 'like', '%' . $searchData . '%')
                    ->orWhereHas('category', function ($q) use ($searchData) {
                        $q->where('category_name', 'like', '%' . $searchData . '%');
                    })
                    ->orWhereHas('division', function ($q) use ($searchData) {
                        $q->where('division_name', 'like', '%' . $searchData . '%');
                    });
            })
            ->limit(200)
            ->get();

        // No data found
        if ($products->isEmpty()) {
            return response()->json([
                'status'        => 'nodata',
                'message'       => 'No products found',
                'search_result' => '',
                'totalRecords'  => 0
            ]);
        }

        // Format product data
        $formattedProducts = $products->map(function ($product) {
            return [
                'prod_id'   => $product->id,
                'prod_name' => $product->product_name,
                'cat_id'    => $product->category_id,
                'div_id'    => $product->division_id,
                'cat_name'  => optional($product->category)->category_name,
                'div_name'  => optional($product->division)->division_name,
            ];
        })->values();

        return response()->json([
            'status'        => 'pass',
            'search_result' => $formattedProducts,
            'totalRecords'  => $products->count(),
            'data'          => $formattedProducts,
            'divisions'     => $products->pluck('division.division_name')->filter()->unique()->values(),
            'category'      => $products->pluck('category.category_name')->filter()->unique()->values(),
        ]);
    }
}

