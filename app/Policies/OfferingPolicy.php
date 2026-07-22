<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Offering;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Offering');
    }

    public function view(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('View:Offering');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Offering');
    }

    public function update(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('Update:Offering');
    }

    public function delete(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('Delete:Offering');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Offering');
    }

    public function restore(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('Restore:Offering');
    }

    public function forceDelete(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('ForceDelete:Offering');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Offering');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Offering');
    }

    public function replicate(AuthUser $authUser, Offering $offering): bool
    {
        return $authUser->can('Replicate:Offering');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Offering');
    }

}