<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\University;
use Illuminate\Auth\Access\HandlesAuthorization;

class UniversityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:University');
    }

    public function view(AuthUser $authUser, University $university): bool
    {
        return $authUser->can('View:University');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:University');
    }

    public function update(AuthUser $authUser, University $university): bool
    {
        return $authUser->can('Update:University');
    }

    public function delete(AuthUser $authUser, University $university): bool
    {
        return $authUser->can('Delete:University');
    }

}