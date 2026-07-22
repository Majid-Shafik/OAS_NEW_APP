<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RequestAdjustOffering;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequestAdjustOfferingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RequestAdjustOffering');
    }

    public function view(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('View:RequestAdjustOffering');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RequestAdjustOffering');
    }

    public function update(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('Update:RequestAdjustOffering');
    }

    public function delete(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('Delete:RequestAdjustOffering');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:RequestAdjustOffering');
    }

    public function restore(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('Restore:RequestAdjustOffering');
    }

    public function forceDelete(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('ForceDelete:RequestAdjustOffering');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RequestAdjustOffering');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RequestAdjustOffering');
    }

    public function replicate(AuthUser $authUser, RequestAdjustOffering $requestAdjustOffering): bool
    {
        return $authUser->can('Replicate:RequestAdjustOffering');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RequestAdjustOffering');
    }

}