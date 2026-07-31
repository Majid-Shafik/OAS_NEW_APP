<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Province;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProvincePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Province') || $authUser->isAdmin();
    }

    public function view(AuthUser $authUser, Province $province): bool
    {
        return $authUser->can('View:Province') || $authUser->isAdmin();
    }

    public function create(AuthUser $authUser): bool
    {
        // Prevent addition permanently
        return false;
    }

    public function update(AuthUser $authUser, Province $province): bool
    {
        return $authUser->can('Update:Province') || $authUser->isAdmin();
    }

    public function delete(AuthUser $authUser, Province $province): bool
    {
        // Prevent deletion permanently
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        // Prevent deletion permanently
        return false;
    }

    public function restore(AuthUser $authUser, Province $province): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, Province $province): bool
    {
        return false;
    }
}
