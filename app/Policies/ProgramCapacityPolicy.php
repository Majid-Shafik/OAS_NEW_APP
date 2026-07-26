<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProgramCapacity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramCapacityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProgramCapacity');
    }

    public function view(AuthUser $authUser, ProgramCapacity $programCapacity): bool
    {
        return $authUser->can('View:ProgramCapacity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProgramCapacity');
    }

    public function update(AuthUser $authUser, ProgramCapacity $programCapacity): bool
    {
        return $authUser->can('Update:ProgramCapacity');
    }

    public function delete(AuthUser $authUser, ProgramCapacity $programCapacity): bool
    {
        return $authUser->can('Delete:ProgramCapacity');
    }

}