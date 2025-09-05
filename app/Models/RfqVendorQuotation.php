<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqVendorQuotation extends Model
{
    protected $table = 'rfq_vendor_quotations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'vendor_id', // Added vendor_id to allow mass assignment
        'rfq_id',
        'rfq_product_variant_id',
        'price',
        'mrp',
        'discount',
        'buyer_price',
        'specification',
        'vendor_attachment_file',
        'vendor_brand',
        'vendor_remarks',
        'vendor_additional_remarks',
        'vendor_price_basis',
        'vendor_payment_terms',
        'vendor_delivery_period',
        'vendor_price_validity',
        'vendor_dispatch_branch',
        'vendor_currency',
        'buyer_user_id',
        'vendor_user_id',
        'status',
        // 'created_at', // Laravel handles these automatically if timestamps are enabled
        // 'updated_at', // Laravel handles these automatically if timestamps are enabled
    ];

     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rfqProductVariant()
    {
        return $this->belongsTo(RfqProductVariant::class, 'rfq_product_variant_id');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id','user_id');
    }
}
