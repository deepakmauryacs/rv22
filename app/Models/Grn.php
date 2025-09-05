<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grn extends Model
{
    protected $table = 'grns';

    public $timestamps = false;
    protected $casts = [
        'updated_at' => 'datetime',
    ];
    protected $fillable = [
        'order_id', 'po_number', 'company_id', 'stock_id', 'stock_return_for', 'grn_no', 'grn_qty',
        'inventory_id', 'inv_status', 'approved_by', 'order_no', 'order_qty', 'rate', 'grn_buyer_rate',
        'rfq_no', 'grn_type', 'vendor_name', 'vendor_invoice_number', 'vehicle_no_lr_no', 'gross_wt',
        'gst_no', 'frieght_other_charges', 'is_deleted', 'updated_by', 'updated_date'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stock()
    {
        return $this->belongsTo(ReturnStock::class, 'stock_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventories::class, 'inventory_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public static function getNextGrnNumber($companyId)
    {
        $maxGrn = self::where('company_id', $companyId)
                      ->max('grn_no');
        $nextGrnNumber = $maxGrn ? $maxGrn + 1 : 1;
        return $nextGrnNumber;
    }

    public function manualOrder()
    {
        return $this->belongsTo(ManualOrder::class, 'order_id', 'id');
    }
    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function getVendorNameAttribute(){
        return $this->resolveVendorName($this);
    }
    public function resolveVendorName($grn)
    {   
        if($grn->grn_type == 2){
            return $grn->attributes['vendor_name'] ?? null;
        }
        if ($grn->grn_type == 4 && $grn->manualOrder) {
            return optional($grn->manualOrder->vendor)->name;
        }
        if ($grn->grn_type == 1 && $grn->Order) {
            return optional($grn->Order)->vendor->legal_name;
        }
        if ($grn->grn_type == 3 && $grn->stock && $grn->stock->grn) {
            return $this->resolveVendorName($grn->stock->grn);
        }

        return null;
    }
    public function getPoNumberAttribute()
    {
        return $this->resolvePoNumber($this);
    }
    public function resolvePoNumber($grn)
    {
        if($grn->grn_type == 2){
            return $grn->attributes['order_no'] ?? null;
        }
        if ($grn->grn_type == 4 && $grn->manualOrder) {
            return optional($grn->manualOrder)->manual_po_number;
        }
        if ($grn->grn_type == 1 && $grn->Order) {
            return optional($grn->Order)->po_number;
        }
        if ($grn->grn_type == 3 && $grn->stock && $grn->stock->grn) {
            return $this->resolvePoNumber($grn->stock->grn);
        }

        return null;
    }
    public function getCreatedAtAttribute()
    {
        return $this->resolveCreatedAt($this);
    }

    private function resolveCreatedAt($grn)
    {
        if ($grn->grn_type == 2) {
            return $grn->updated_at ?? null;
        }

        if ($grn->grn_type == 4 && $grn->manualOrder) {
            return optional($grn->manualOrder)->created_at;
        }

        if ($grn->grn_type == 1 && $grn->order) {
            return optional($grn->order)->created_at;
        }

        if ($grn->grn_type == 3 && $grn->stock && $grn->stock->grn) {
            return $this->resolveCreatedAt($grn->stock->grn);
        }
       
        return null;
    }


    public function manualOrderProduct()
    {
        return $this->hasOne(ManualOrderProduct::class, 'manual_order_id', 'order_id')
            ->whereColumn('manual_order_products.inventory_id', 'inventory_id');
    }
    public function getRatesAttribute()
    {    
        if ($this->grn_type == 4 && $this->manualOrderProduct) {
            return $this->manualOrderProduct->product_price;
        }

        return null;
    }
    public function getOrderRateAttribute()
    {
        return $this->resolveOrderRate($this);
    }

    private function resolveOrderRate($grn)
    {
        if($grn->order_id=='0' && $grn->stock_return_for=='0'){
            return $grn->inventory->stock_price;
        }
        if ($grn->grn_type == 4 && $grn->manualOrderProduct) {
            return $grn->manualOrderProduct->product_price;
        }
        if ($grn->grn_type == 2) {
            return $grn->rate;
        }

        if ($grn->grn_type == 1 && $grn->order && $grn->inventory) {
            if($grn->grn_buyer_rate){
                return $grn->grn_buyer_rate;
            }
            return $grn->order->order_variants
                ->firstWhere('product_id', $grn->inventory->product_id)?->order_price;
        }

        if ($grn->grn_type == 3 && $grn->stock && $grn->stock->grn) {
            return $this->resolveOrderRate($grn->stock->grn);
        }

        return null;
    }


    public function getOrderQtyAttribute()
    {
        if ($this->grn_type == 4 && $this->manualOrderProduct) {
            return $this->manualOrderProduct->product_quantity;
        }
        if ($this->grn_type == 1 && $this->Order) {
            return $this->Order->order_variants->firstWhere('product_id', $this->inventory->product_id)?->order_quantity;        
        }

        return null;
    }
    public function getRfqIdAttribute()
    {
        
        if ($this->grn_type == 1 && $this->Order) {
            return $this->Order->rfq_id;        
        }

        return null;
    }

    
    




}

