<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendorAuctionPrice extends Model
{
    protected $table = 'rfq_vendor_auction_price';

    public function rfqProductVariant()
    {
        return $this->belongsTo(RfqProductVariant::class, 'rfq_product_variant_id');
    }
}
