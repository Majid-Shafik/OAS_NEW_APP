<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HighSchoolDegreeBType;
use Illuminate\Auth\Access\HandlesAuthorization;

class HighSchoolDegreeBTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HighSchoolDegreeBType');
    }

    public function view(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        return $authUser->can('View:HighSchoolDegreeBType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HighSchoolDegreeBType');
    }

    public function update(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        return $authUser->can('Update:HighSchoolDegreeBType');
    }

    public function delete(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        return $authUser->can('Delete:HighSchoolDegreeBType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HighSchoolDegreeBType');
    }

    public function showWithCertificate(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        return $authUser->can('ShowWithCertificate:HighSchoolDegreeBType');
    }

    public function approve(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        return $authUser->can('Approve:HighSchoolDegreeBType');
    }

}