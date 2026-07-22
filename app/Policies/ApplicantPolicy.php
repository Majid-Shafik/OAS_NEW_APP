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

    public function restore(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('Restore:Applicant');
    }

    public function forceDelete(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('ForceDelete:Applicant');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Applicant');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Applicant');
    }

    public function replicate(AuthUser $authUser, Applicant $applicant): bool
    {
        return $authUser->can('Replicate:Applicant');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Applicant');
    }

}