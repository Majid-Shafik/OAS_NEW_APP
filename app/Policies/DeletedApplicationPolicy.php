<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DeletedApplication;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeletedApplicationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DeletedApplication');
    }

    public function view(AuthUser $authUser, DeletedApplication $deletedApplication): bool
    {
        return $authUser->can('View:DeletedApplication');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DeletedApplication');
    }

    public function update(AuthUser $authUser, DeletedApplication $deletedApplication): bool
    {
        return $authUser->can('Update:DeletedApplication');
    }

    public function delete(AuthUser $authUser, DeletedApplication $deletedApplication): bool
    {
        return $authUser->can('Delete:DeletedApplication');
    }

}