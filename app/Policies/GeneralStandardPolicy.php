<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\GeneralStandard;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneralStandardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:GeneralStandard');
    }

    public function view(AuthUser $authUser, GeneralStandard $generalStandard): bool
    {
        return $authUser->can('View:GeneralStandard');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:GeneralStandard');
    }

    public function update(AuthUser $authUser, GeneralStandard $generalStandard): bool
    {
        return $authUser->can('Update:GeneralStandard');
    }

    public function delete(AuthUser $authUser, GeneralStandard $generalStandard): bool
    {
        return $authUser->can('Delete:GeneralStandard');
    }

}