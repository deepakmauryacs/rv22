<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;


class BranchDetail extends Model
{
    use HasFactory;

    protected $table = 'branch_details';
    protected $fillable = [
        'branch_id',
        'user_type',
        'record_type',
        'status',
        'is_regd_address',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getDistinctActiveBranchesByUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 1)
            ->where('record_type', 1)
            ->groupBy('branch_id')
            ->select('branch_id', DB::raw('MIN(name) as name'))
            ->orderBy('name', 'asc')
            ->get();
    }


    protected static function booted()
    {
        static::addGlobalScope('alphabeticalFirst', function (Builder $builder) {
            $builder->whereIn('branch_details.id', function ($query) {
                $query->select(DB::raw('MIN(bd1.id)'))
                    ->from('branch_details as bd1')
                    ->join(DB::raw('
                        (
                            SELECT branch_id, MIN(name) AS name
                            FROM branch_details
                            WHERE record_type = 1 AND status = 1
                            GROUP BY branch_id
                        ) as bd2
                    '), function ($join) {
                        $join->on('bd1.branch_id', '=', 'bd2.branch_id')
                            ->on('bd1.name', '=', 'bd2.name');
                    })
                    ->where('bd1.record_type', 1)
                    ->where('bd1.status', 1)
                    ->groupBy('bd1.branch_id');
            });
        });
    }
     public function branch_country()
    {
        return $this->hasOne(Country::class, 'id', 'country');
    }
    public function branch_state()
    {
        return $this->hasOne(State::class, 'id','state');
    }

    public function branch_city()
    {
        return $this->hasOne(City::class, 'id', 'city');
    }
}
