<?php

namespace App\Policies;

use App\Models\DeletedApplication;
use App\Models\User;

class DeletedApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_deleted::application');
    }

    public function view(User $user, DeletedApplication $deletedApplication): bool
    {
        return $user->can('view_deleted::application');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, DeletedApplication $deletedApplication): bool
    {
        return false;
    }

    public function delete(User $user, DeletedApplication $deletedApplication): bool
    {
        return false;
    }

    public function restore(User $user, DeletedApplication $deletedApplication): bool
    {
        return $user->can('restore_deleted::application');
    }

    public function forceDelete(User $user, DeletedApplication $deletedApplication): bool
    {
        return false;
    }
}
