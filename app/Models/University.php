<?php

namespace App\Models;

use App\Traits\HasUniversityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasUniversityScope;

    protected $table = 'university';

    protected $primaryKey = 'UNID';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'UNID', 'NEW_ID', 'U_NAME', 'NEW_NAME', 'EN_U_NAME', 'IS_IT_ENABLE', 'CLEARING_UN',
    ];

    public function scopeClearing(Builder $query): Builder
    {
        return $query->where('CLEARING_UN', 1);
    }

    public function scopeCoordination(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('CLEARING_UN', '!=', 1)->orWhereNull('CLEARING_UN');
        });
    }
}
