<?php

namespace App\Policies;

use Filament\Actions\Exports\Models\Export;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the export.
     *
     * @param  \App\Models\User  $user
     * @param  \Filament\Actions\Exports\Models\Export  $export
     * @return bool
     */
    public function view(User $user, Export $export)
    {
        // Compare loosely to avoid any string/int strict comparison issues
        // and explicitly use the user_USER_IDENT column.
        return $user->USER_IDENT == $export->user_USER_IDENT;
    }
}
