<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendorAuctionPrice extends Model
{
    protected $table = 'rfq_vendor_auction_price';

    public function rfqProductVariant()
    {
        // Database column is misspelled as `rfq_product_veriant_id`
        // so we need to reference it explicitly here.
        return $this->belongsTo(RfqProductVariant::class, 'rfq_product_veriant_id');
    }
}
