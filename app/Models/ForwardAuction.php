<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForwardAuction extends Model
{
    protected $table = 'forward_auctions';

    protected $fillable = [
        'auction_id',
        'buyer_id',
        'buyer_user_id',
        'schedule_date',
        'schedule_start_time',
        'schedule_end_time',
        'buyer_branch',
        'branch_address',
        'remarks',
        'price_basis',
        'payment_terms',
        'delivery_period',
        'currency',
    ];

    public function auction()
    {
        return $this->belongsTo(ForwardAuction::class, 'forward_auction_id');
    }

    public function products()
    {
        return $this->hasMany(ForwardAuctionProduct::class, 'auction_id', 'auction_id');
    }

    public function buyerUser()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'forward_auction_vendors', 'auction_id', 'vendor_id');
    }
}