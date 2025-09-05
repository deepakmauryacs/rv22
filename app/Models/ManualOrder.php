<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualOrder extends Model
{
    protected $fillable = [
        'manual_po_number', 'vendor_id', 'buyer_id', 'buyer_user_id',
        'order_status', 'order_price_basis', 'order_payment_term',
        'order_delivery_period', 'order_remarks', 'order_add_remarks',
        'prepared_by', 'approved_by',
    ];
    public function products(): HasMany
    {
        return $this->hasMany(ManualOrderProduct::class, 'manual_order_id');
    }
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /*public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }*/

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id', 'user_id');
    }


    public function buyerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }
    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getBranchIdAttribute()
    {
        return $this->products->first()?->inventory?->branch_id;
    }

    public function getBranchNameAttribute()
    {
        return $this->products->first()?->inventory?->branch?->name;
    }

    public function order_products()
    {
        return $this->hasMany(ManualOrderProduct::class, 'manual_order_id', 'id');
    }

   
}
