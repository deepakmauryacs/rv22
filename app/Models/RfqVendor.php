<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendor extends Model
{   

    protected $table = 'rfq_vendors';
    
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_user_id');
    }
    public function vendorMainProduct()
    {
        return $this->hasMany(VendorProduct::class, 'vendor_id', 'vendor_user_id')
                    ->where('vendor_status', 1)
                    ->where('edit_status', 0)
                    ->where('approval_status', 1)
                    ->orderByDesc('id')
                    ->take(3);
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class, 'rfq_id');
    }

    public function rfqVendorProfile()
    {
        return $this->hasOne(Vendor::class, 'user_id', 'vendor_user_id');
    }
    
    public function rfqVendorDetails()
    {
        return $this->hasOne(User::class, 'id', 'vendor_user_id');
    }

    public function vendorOrders()
    {
        return $this->hasMany(Order::class, 'vendor_id', 'vendor_user_id');
    }
    public function vendorFavorites()
    {
        return $this->hasMany(BuyerPreference::class, 'vend_user_id', 'vendor_user_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function productVendors()
    {
        return $this->hasMany(VendorProduct::class, 'product_id', 'product_id');
    }

}
