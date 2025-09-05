<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForwardAuctionVendor extends Model
{
    protected $fillable = [
        'auction_id',
        'auction_product_id',
        'vendor_id',
    ];
}
