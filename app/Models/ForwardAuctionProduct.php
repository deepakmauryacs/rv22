<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForwardAuctionProduct extends Model
{
    protected $fillable = [
        'auction_id',
        'product_name',
        'specs',
        'quantity',
        'uom',
        'start_price',
        'min_bid_increment_amount',
        'file_attachment',
    ];
}
