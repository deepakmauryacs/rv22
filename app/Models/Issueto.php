<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Issueto extends Model
{
    use HasFactory;

    protected $table = 'issue_to';

    protected $fillable = [
        'user_id',
        'name',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;
    protected static function booted()
    {
        static::addGlobalScope('user_id_filter', function (Builder $builder) {
            $builder->select('id', 'name')
                    ->where('user_id', Auth::id());
        });
    }
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
