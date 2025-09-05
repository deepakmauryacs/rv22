<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersPi extends Model
{
    protected $table = 'orders_pi';

    public function vendorUser()
    {
        return $this->hasOne(User::class, 'id', 'vendor_id');
    }
    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'vendor_id');
    }
}
