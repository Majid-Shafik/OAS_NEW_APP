<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:UserGroup');
    }

    public function view(AuthUser $authUser, UserGroup $userGroup): bool
    {
        return $authUser->can('View:UserGroup');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
        return $authUser->can('Create:UserGroup');
    }

    public function update(AuthUser $authUser, UserGroup $userGroup): bool
    {
        return false;
        return $authUser->can('Update:UserGroup');
    }

    public function delete(AuthUser $authUser, UserGroup $userGroup): bool
    {
        return false;
        return $authUser->can('Delete:UserGroup');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
        return $authUser->can('DeleteAny:UserGroup');
    }
}
