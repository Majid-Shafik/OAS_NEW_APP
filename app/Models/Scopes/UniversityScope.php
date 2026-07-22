<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UniversityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Only apply scope if a user is authenticated and already resolved (prevents infinite loop on User auth)
        if (Auth::hasUser()) {
            $user = Auth::user();

            if ($user->UNID == 0) {
                // Super admin: filter by selected_unid
                // if (get_class($model) !== \App\Models\University::class) {
                $selectedUnid = session('selected_unid', 0);
                if ($selectedUnid != 0) {
                    $builder->where($model->getTable().'.UNID', $selectedUnid);
                }
                // }
            } else {
                // University admin/user
                $builder->where($model->getTable().'.UNID', $user->UNID);
            }
        }
    }
}
