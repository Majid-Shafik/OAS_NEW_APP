<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class ClearingApplicant extends Applicant
{
    protected $table = 'applicant';

    protected static function booted()
    {
        parent::booted();

        static::addGlobalScope('is_clearing', function (Builder $builder) {
            $builder->where('IS_CLEARING', 1);
        });

        static::creating(function ($model) {
            $model->IS_CLEARING = \App\Enums\IsClearingType::CLEARING;
        });
    }
}
