<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqProductVariant extends Model
{
    protected $guarded = [];

    public function masterProduct()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function uoms()
    {
        return $this->belongsTo(Uom::class, 'uom');
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id', 'rfq_id');
    }

    // public function masterProduct()
    // {
    //     return $this->hasOne(Product::class, 'id', 'product_id');
    // }
    //start Inventory
    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    //end Inventory

    // In your Variant model (likely RfqProductVariant or similar)
    public function vendor_quotation()
    {
        return $this->hasOne(RfqVendorQuotation::class, 'rfq_product_variant_id')
            ->where('vendor_id', getParentUserId())
            ->latest(); // gets the most recent quotation
    }

    // In your Variant model (likely RfqProductVariant or similar)
    // public function vendor_quotation()
    // {
    //     return $this->hasOne(RfqVendorQuotation::class, 'rfq_product_variant_id')
    //         ->where('vendor_id', getParentUserId())
    //         ->latest(); // gets the most recent quotation
    // }

    // All auction price rows for this variant
    public function vendorAuctionPrices()
    {
        // NOTE the column name is 'rfq_product_veriant_id' (typo in schema)
        return $this->hasMany(RfqVendorAuctionPrice::class, 'rfq_product_veriant_id', 'id');
    }

    // Latest auction price row (we’ll still filter via eager-load closure)
    public function latestVendorAuctionPrice()
    {
        // Note: use the actual relation class & key names in your app
        return $this->hasOne(\App\Models\RfqVendorAuctionPrice::class, 'rfq_product_veriant_id', 'id')
            ->orderByDesc('id'); // no latestOfMany here
    }


    // One auction row for THIS variant in a specific auction
    public function auctionVariant()
    {
        // fk = rfq_auction_variants.rfq_variant_id
        // local key = rfq_product_variants.id    (NOT variant_grp_id)
        return $this->hasOne(RfqAuctionVariant::class, 'rfq_variant_id', 'id');
    }

    // (optional) all auction rows for this variant across auctions
    public function auctionVariants()
    {
        return $this->hasMany(RfqAuctionVariant::class, 'rfq_variant_id', 'id');
    }
    public function latestVendorQuotation($vendorId)
    {
        return $this->vendorQuotations()
            ->where('vendor_id', $vendorId)
            ->latest()
            ->first();
    }
    // In your Variant model (likely RfqProductVariant or similar)
    // public function vendor_quotations()
    // {
    //     return $this->hasOne(RfqVendorQuotation::class, 'rfq_product_variant_id')
    //                //->where('vendor_id', getParentUserId())
    //                ->latest(); // gets the most recent quotation
    // }

    public function vendorQuotations()
    {
        return $this->hasMany(RfqVendorQuotation::class, 'rfq_product_variant_id');
    }

    // latest single quotation for this vendor (you already had similar)
    // public function vendor_quotation()
    // {
    //     return $this->hasOne(\App\Models\RfqVendorQuotation::class, 'rfq_product_variant_id', 'id')
    //         ->where('vendor_id', getParentUserId())
    //         ->orderByDesc('id');
    // }

    // ALL quotations for history (same table) – used for buyer counter offers
    public function buyer_counter_offers()
    {
        return $this->hasMany(\App\Models\RfqVendorQuotation::class, 'rfq_product_variant_id', 'id')
            ->where('vendor_id', getParentUserId())
            ->whereNotNull('buyer_price')
            ->where('buyer_price', '>', 0)
            ->orderByDesc('id');
    }

    // ALL vendor price history (your own quoted prices)
    public function vendor_price_history()
    {
        return $this->hasMany(\App\Models\RfqVendorQuotation::class, 'rfq_product_variant_id', 'id')
            ->where('vendor_id', getParentUserId())
            ->orderByDesc('id');
    }
}
