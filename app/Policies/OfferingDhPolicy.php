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

}