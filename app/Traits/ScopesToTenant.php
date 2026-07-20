<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScopesToTenant
{
    protected static function bootScopesToTenant()
    {
        static::addGlobalScope('tenant_unid', function (Builder $builder) {
            if (session()->has('tenant_unid')) {
                $builder->where('UNID', session('tenant_unid'));
            }
        });

        static::creating(function ($model) {
            if (session()->has('tenant_unid')) {
                $model->UNID = session('tenant_unid');
            }
        });
    }
}
