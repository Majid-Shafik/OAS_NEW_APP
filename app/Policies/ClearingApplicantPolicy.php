<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ClearingApplicant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClearingApplicantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClearingApplicant');
    }

    public function view(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        return $authUser->can('View:ClearingApplicant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClearingApplicant');
    }

    public function update(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if ($clearingApplicant->STATUS === \App\Enums\ApplicantStatus::Ready && !$authUser->hasRole(['super_admin', 'admin'])) {
            return false;
        }
        
        return $authUser->can('Update:ClearingApplicant');
    }

    public function delete(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        return $authUser->can('Delete:ClearingApplicant');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClearingApplicant');
    }

    public function firstReview(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        return $authUser->can('FirstReview:ClearingApplicant');
    }

    public function secondReview(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        return $authUser->can('SecondReview:ClearingApplicant');
    }

    public function showClearingAttachments(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        return $authUser->can('ShowClearingAttachments:ClearingApplicant');
    }
}