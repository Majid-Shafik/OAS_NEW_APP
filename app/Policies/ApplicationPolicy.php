<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Application;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Application');
    }

    public function view(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('View:Application');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Application');
    }

    public function update(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Update:Application');
    }

    public function delete(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Delete:Application');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Application');
    }

    public function accept(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Accept:Application');
    }

    public function pay(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Pay:Application');
    }

    public function confirm(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Confirm:Application');
    }

}