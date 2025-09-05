<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\LiveVendorProduct;
use App\Models\RfqProduct;

class SearchProductController extends Controller
{
    public function searchVendorActiveProduct(Request $request)
    {
        $product_name = $request->product_name;
        $source = $request->source;
        
        if(empty($product_name)){
            return response()->json([
                'status' => false,
                'message' => 'Please enter product name'
            ]);
        }
        $page = $request->page;

        $draft_products = array();

        if(!empty($source) && $source=='rfq'){
            $rfq_id = $request->rfq_id;
            $draft_products = RfqProduct::where('rfq_id', $rfq_id)->select('product_id')->pluck('product_id')->toArray();
        }

        $product_name = LiveVendorProduct::cleanString($product_name);

        $search_arr = array(
            'product_name' => $product_name,
            'per_page' => 20,
            'page' => $page,
            'draft_products' => $draft_products,
        )
        ;
        $is_suggesation = $request->is_suggesation;
        
        if($is_suggesation=="no"){
            $products = LiveVendorProduct::getSearchedProduct($search_arr);

            if(empty($products)){
                $is_suggesation=="yes";
                $products = LiveVendorProduct::getSuggesationSearchedProduct($search_arr);
            }
        }else{
            $is_suggesation=="yes";
            $products = LiveVendorProduct::getSuggesationSearchedProduct($search_arr);
        }
        // echo "<pre>";
        // print_r($products);
        // die;
        $product_html = view('buyer.vendor-product.search-product-item', compact('products', 'page', 'is_suggesation', 'product_name', 'source'))->render();


        return response()->json([
            'status' => true,
            'message' => 'Product found',
            'is_products' => count($products)>0 ? true : false,
            'product_html' => $product_html,
            'is_suggesation' => $is_suggesation,
            // 'products' => count($products),
        ]);
    }

    public function getSearchByDivision(Request $request)
    {
        $divisions = LiveVendorProduct::with([
            'division' => function ($q) {
                $q->select('id', 'division_name');
                $q->where('status', 1);
                $q->orderBy("division_name", "ASC");
            },
            'category' => function ($q) {
                $q->select('id', 'category_name');
                $q->where('status', 1);
                $q->orderBy("category_name", "ASC");
            }
        ])
        ->select('division_id', 'category_id')
        ->groupBy('division_id', 'category_id')
        ->get();
        
        $divisionCategoryData = [];

        foreach ($divisions as $item) {
            $division = $item->division;
            $category = $item->category;

            if ($division && $category) {
                $divisionId = $division->id;
                $divisionName = $division->division_name;
                $categoryId = $category->id;
                $categoryName = $category->category_name;

                if (!isset($divisionCategoryData[$divisionId])) {
                    $divisionCategoryData[$divisionId] = [
                        'division_id' => $divisionId,
                        'division_name' => $divisionName,
                        'categories' => [],
                    ];
                }

                // Avoid duplicate categories
                $exists = collect($divisionCategoryData[$divisionId]['categories'])
                    ->contains('category_id', $categoryId);

                if (!$exists) {
                    $divisionCategoryData[$divisionId]['categories'][] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                    ];
                }
            }
        }

        // Sort categories inside each division by category_name ASC
        foreach ($divisionCategoryData as &$division) {
            usort($division['categories'], function ($a, $b) {
                return strcmp($a['category_name'], $b['category_name']);
            });
        }
        unset($division); // break reference

        $divisions_html = view('buyer.layouts.search-by-division', compact('divisionCategoryData'))->render();

        return response()->json([
            // 'status' => true,
            'divisions' => $divisions_html,
        ]);
    }
}
