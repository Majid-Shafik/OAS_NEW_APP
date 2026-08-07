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

    protected function checkUniversityAccess(AuthUser $authUser, HighSchoolDegreeBType $record): bool
    {
        if ($authUser->UNID != 0 && $record->UNID != 0 && (int)$record->UNID !== (int)$authUser->UNID) {
            return false;
        }
        
        $selectedUnid = (int)session('selected_unid', 0);
        if ($authUser->UNID == 0 && $selectedUnid !== 0 && $record->UNID != 0 && (int)$record->UNID !== $selectedUnid) {
            return false;
        }

        return true;
    }

    public function view(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        if (!$this->checkUniversityAccess($authUser, $highSchoolDegreeBType)) {
            return false;
        }

        return $authUser->can('View:HighSchoolDegreeBType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HighSchoolDegreeBType');
    }

    public function update(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        if (!$this->checkUniversityAccess($authUser, $highSchoolDegreeBType)) {
            return false;
        }

        return $authUser->can('Update:HighSchoolDegreeBType');
    }

    public function delete(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        if (!$this->checkUniversityAccess($authUser, $highSchoolDegreeBType)) {
            return false;
        }

        return $authUser->can('Delete:HighSchoolDegreeBType');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HighSchoolDegreeBType');
    }

    public function showWithCertificate(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        if (!$this->checkUniversityAccess($authUser, $highSchoolDegreeBType)) {
            return false;
        }

        return $authUser->can('ShowWithCertificate:HighSchoolDegreeBType');
    }

    public function approve(AuthUser $authUser, HighSchoolDegreeBType $highSchoolDegreeBType): bool
    {
        if (!$this->checkUniversityAccess($authUser, $highSchoolDegreeBType)) {
            return false;
        }

        return $authUser->can('Approve:HighSchoolDegreeBType');
    }

}