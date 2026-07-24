<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Faculty;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacultyPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Faculty');
    }

    public function view(AuthUser $authUser, Faculty $faculty): bool
    {
        return $authUser->can('View:Faculty');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Faculty');
    }

    public function update(AuthUser $authUser, Faculty $faculty): bool
    {
        return $authUser->can('Update:Faculty');
    }

    public function delete(AuthUser $authUser, Faculty $faculty): bool
    {
        return $authUser->can('Delete:Faculty');
    }

}