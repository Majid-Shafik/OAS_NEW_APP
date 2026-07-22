<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OfferingDh;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfferingDhPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OfferingDh');
    }

    public function view(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('View:OfferingDh');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OfferingDh');
    }

    public function update(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('Update:OfferingDh');
    }

    public function delete(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('Delete:OfferingDh');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OfferingDh');
    }

    public function restore(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('Restore:OfferingDh');
    }

    public function forceDelete(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('ForceDelete:OfferingDh');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OfferingDh');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OfferingDh');
    }

    public function replicate(AuthUser $authUser, OfferingDh $offeringDh): bool
    {
        return $authUser->can('Replicate:OfferingDh');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OfferingDh');
    }

}