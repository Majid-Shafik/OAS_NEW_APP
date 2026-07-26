<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProgramCapacityHistory;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramCapacityHistoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProgramCapacityHistory');
    }

    public function view(AuthUser $authUser, ProgramCapacityHistory $programCapacityHistory): bool
    {
        return $authUser->can('View:ProgramCapacityHistory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProgramCapacityHistory');
    }

    public function update(AuthUser $authUser, ProgramCapacityHistory $programCapacityHistory): bool
    {
        return $authUser->can('Update:ProgramCapacityHistory');
    }

    public function delete(AuthUser $authUser, ProgramCapacityHistory $programCapacityHistory): bool
    {
        return $authUser->can('Delete:ProgramCapacityHistory');
    }

}