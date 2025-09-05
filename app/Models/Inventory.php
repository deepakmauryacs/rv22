<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    //
    public function branch()
    {
        return $this->belongsTo(BranchDetail::class, 'buyer_branch_id', 'branch_id');
    }
}
