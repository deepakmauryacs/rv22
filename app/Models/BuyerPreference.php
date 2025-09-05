<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuyerPreference extends Model
{
    protected $table = 'buyer_preferences';
    public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo(User::class, 'vend_user_id', 'id');
    }
}
