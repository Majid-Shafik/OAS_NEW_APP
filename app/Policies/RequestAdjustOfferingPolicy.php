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

}