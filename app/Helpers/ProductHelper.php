<?php

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Illuminate\Support\Str;

if (!function_exists('validate_product_tags')) {
    /**
     * Validate product tags/aliases for uniqueness across the system
     *
     * @param string|null $tags Comma-separated list of tags
     * @param int|null $productId Current product ID (for updates)
     * @param int|null $vendorId Current vendor ID
     * @param bool $isNew Whether this is for a new product
     * @return array Array of error messages
     */
    function validate_product_tags(
        ?string $tags,
        ?int $productId = null,
        ?int $vendorId = null,
        bool $isNew = false
    ): array {
        $errors = [];

        if (empty($tags)) {
            return $errors;
        }

        // Normalize and prepare tags
        $tags = collect(explode(',', $tags))
            ->map(fn ($tag) => Str::upper(trim($tag)))
            ->unique()
            ->filter()
            ->map(function ($tag) {
                $tag = substr($tag, 0, 255);
                return Str::upper(preg_replace('/\s+/', ' ', trim($tag)));
            });

        foreach ($tags as $tag) {
            // Check against master products
            $masterProduct = DB::table('products')->where('product_name', $tag)->first();
            if ($masterProduct) {
                $errors[] = "<b>{$tag}</b> is already a Master Product <b>{$masterProduct->product_name}</b>.";
                continue;
            }

            // Check against master product aliases
            $masterAlias = DB::table('product_alias')
                ->where('alias', $tag)
                ->where('alias_of', 1)
                ->first();

            if ($masterAlias) {
                $productName = get_product_name_by_prod_id($masterAlias->product_id);
                $errors[] = "<b>{$tag}</b> already used as alias for Master Product <b>{$productName}</b>.";
                continue;
            }

            // Check against vendor product aliases
            $vendorAliasQuery = DB::table('product_alias')
                ->where('alias', $tag)
                ->where('alias_of', 2);

            if ($isNew) {
                $vendorAliasQuery->where('is_new', true);
            }

            $vendorAliasQuery->where(function ($query) use ($productId, $vendorId) {
                $query->where('product_id', '!=', $productId)
                    ->orWhere('vendor_id', '!=', $vendorId);
            });

            $vendorAlias = $vendorAliasQuery->first();

            if ($vendorAlias && !empty($productId)) {
                $vendorName = get_vendor_name_by_vend_id($vendorAlias->vendor_id);
                $productName = get_product_name_by_prod_id($vendorAlias->product_id);
                $errors[] = "<b>{$tag}</b> already used by Vendor <b>{$vendorName}</b> as an alias for Product <b>{$productName}</b>.";
                continue;
            }
        }

        return $errors;
    }
}

if (!function_exists('get_product_name_by_prod_id')) {
    function get_product_name_by_prod_id(int $productId): ?string
    {
        return Product::where('id', $productId)->value('product_name');
    }
}

if (!function_exists('get_vendor_name_by_vend_id')) {
    function get_vendor_name_by_vend_id(int $vendorId): ?string
    {
        return DB::table('users')->where('id', $vendorId)->value('name');
    }
}

if (!function_exists('get_alias_master_by_prod_id')) {
    function get_alias_master_by_prod_id($prod_id) {
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 1)
            ->where('is_new', 1)
            ->where('product_id', $prod_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        return implode(', ', $aliases);
    }
}

if (!function_exists('get_alias_vendor_by_prod_id')) {
    function get_alias_vendor_by_prod_id($prod_id, $vend_id) {
        // Fetch aliases from the database
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 2)
            ->where('is_new', 1)
            ->where('product_id', $prod_id)
            ->where('vendor_id', $vend_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        // Concatenate aliases into a single string
        return implode(', ', $aliases);
    }
}

if (!function_exists('get_new_alias_vendor_by_prod_id')) {
    function get_new_alias_vendor_by_prod_id($prod_id, $vend_id) {
        // Fetch aliases from the database
        $aliases = DB::table('product_alias')
            ->select('alias')
            ->where('alias_of', 2)
            ->where('is_new',null)
            ->where('product_id', $prod_id)
            ->where('vendor_id', $vend_id)
            ->get()
            ->pluck('alias')
            ->toArray();

        // Concatenate aliases into a single string
        return implode(', ', $aliases);
    }
}




if (!function_exists('get_active_dealer_types')) {
    function get_active_dealer_types()
    {
        return DB::table('dealer_types')
            ->where('status', '1')
            ->orderBy('dealer_type')
            ->get();
    }
}

if (!function_exists('get_active_tax')) {

    function get_active_tax()
    {
        return DB::table('taxes')
            ->where('status', '1')
            ->orderBy('tax_name')
            ->get();
    }
}


if (!function_exists('createSlug')) {

    function createSlug($string)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}
