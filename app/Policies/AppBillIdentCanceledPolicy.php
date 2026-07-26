<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AppBillIdentCanceled;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppBillIdentCanceledPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AppBillIdentCanceled');
    }

    public function view(AuthUser $authUser, AppBillIdentCanceled $appBillIdentCanceled): bool
    {
        return $authUser->can('View:AppBillIdentCanceled');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
        return $authUser->can('Create:AppBillIdentCanceled');
    }
    
    public function update(AuthUser $authUser, AppBillIdentCanceled $appBillIdentCanceled): bool
    {
        return false;
        return $authUser->can('Update:AppBillIdentCanceled');
    }
    
    public function delete(AuthUser $authUser, AppBillIdentCanceled $appBillIdentCanceled): bool
    {
        return false;
        return $authUser->can('Delete:AppBillIdentCanceled');
    }

}