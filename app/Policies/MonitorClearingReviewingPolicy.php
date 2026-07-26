<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MonitorClearingReviewing;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonitorClearingReviewingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MonitorClearingReviewing');
    }

    public function view(AuthUser $authUser, MonitorClearingReviewing $monitorClearingReviewing): bool
    {
        return $authUser->can('View:MonitorClearingReviewing');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MonitorClearingReviewing');
    }

    public function update(AuthUser $authUser, MonitorClearingReviewing $monitorClearingReviewing): bool
    {
        return $authUser->can('Update:MonitorClearingReviewing');
    }

    public function delete(AuthUser $authUser, MonitorClearingReviewing $monitorClearingReviewing): bool
    {
        return $authUser->can('Delete:MonitorClearingReviewing');
    }

}