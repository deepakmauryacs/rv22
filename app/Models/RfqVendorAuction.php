<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendorAuction extends Model
{
    public function rfq_auction()
    {
        return $this->belongsTo(RfqAuction::class,'auction_id','id');
    }

    public function vendor()
    {
        // vendor_id references the vendors.user_id column
        return $this->belongsTo(Vendor::class, 'vendor_id', 'user_id');
    }
}
