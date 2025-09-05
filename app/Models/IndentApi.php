<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndentApi extends Model
{
    protected $table = 'indent_api';
    protected $guarded = [];

    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'match_product_id', 'id');
    }

    public function getUom()
    {
        return $this->belongsTo(Uom::class, 'uom', 'id');
    }


    public function rfqVariants()
    {
        return $this->hasMany(RfqProductVariant::class, 'api_id', 'id');
    }

    public function getRfqQuantitySum()
    {
        return $this->rfqVariants()
            ->join('rfqs', 'rfq_product_variants.rfq_id', '=', 'rfqs.rfq_id')
            // ->where('rfqs.record_type', 2)
            // ->whereNotIn('rfqs.buyer_rfq_status', ['8', '10'])
            ->sum('rfq_product_variants.quantity');
    }
}