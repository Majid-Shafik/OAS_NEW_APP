<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Applicant;
use App\Models\ClearingApplicant;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ClearingApplicantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClearingApplicant');
    }

    protected function checkUniversityAccess(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if ($authUser->UNID != 0 && (int)$clearingApplicant->UNID !== (int)$authUser->UNID) {
            return false;
        }
        
        $selectedUnid = (int)session('selected_unid', 0);
        if ($authUser->UNID == 0 && $selectedUnid !== 0 && (int)$clearingApplicant->UNID !== $selectedUnid) {
            return false;
        }

        return true;
    }

    public function view(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('View:ClearingApplicant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClearingApplicant');
    }

    public function update(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        if ($clearingApplicant->STATUS === \App\Enums\ApplicantStatus::Ready && !$authUser->hasRole(['super_admin', 'admin'])) {
            return false;
        }
        
        return $authUser->can('Update:ClearingApplicant');
    }

    public function delete(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('Delete:ClearingApplicant');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClearingApplicant');
    }

    public function firstReview(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('FirstReview:ClearingApplicant');
    }

    public function secondReview(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('SecondReview:ClearingApplicant');
    }

    public function reReviewFirst(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('ReReviewFirst:ClearingApplicant');
    }

    public function reReviewSecond(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('ReReviewSecond:ClearingApplicant');
    }

    public function showClearingAttachments(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('ShowClearingAttachments:ClearingApplicant');
    }

    public function convertToApplicant(AuthUser $authUser, ClearingApplicant $clearingApplicant): bool
    {
        if (!$this->checkUniversityAccess($authUser, $clearingApplicant)) {
            return false;
        }

        return $authUser->can('ConvertToApplicant:ClearingApplicant');
    }
}