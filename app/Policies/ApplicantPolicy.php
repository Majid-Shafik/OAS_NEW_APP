<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Applicant;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Applicant');
    }

    public function view(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('View:Applicant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Applicant');
    }

    public function update(AuthUser $authUser, Applicant $applicant): bool
    {
        if ($applicant->STATUS === \App\Enums\ApplicantStatus::Ready && !$authUser->hasRole(['super_admin', 'admin'])) {
            return false;
        }
        
        return $authUser->can('Update:Applicant');
    }

    public function delete(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('Delete:Applicant');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Applicant');
    }

    public function updateFromMinistryApplicant(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('UpdateFromMinistryApplicant:Applicant');
    }

    public function convertToClearing(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('ConvertToClearing:Applicant');
    }

    public function completeFile(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('CompleteFile:Applicant');
    }

}