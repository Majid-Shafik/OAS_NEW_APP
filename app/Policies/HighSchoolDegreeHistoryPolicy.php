<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HighSchoolDegreeHistory;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class HighSchoolDegreeHistoryPolicy
{
    use HandlesAuthorization;

    /**
     * هل يملك صلاحية رؤية القائمة ودخول الصفحة؟
     */
    public function viewAny(AuthUser $authUser): bool
    {
        // التحقق من الصلاحية أو كونه أدمن
        return $authUser->can('ViewAny:HighSchoolDegreeHistory') || $authUser->isAdmin();
    }

    /**
     * هل يملك صلاحية فتح تفاصيل سجل معين؟
     */
    public function view(AuthUser $authUser, HighSchoolDegreeHistory $record): bool
    {
        return $authUser->can('View:HighSchoolDegreeHistory') || $authUser->isAdmin();
    }

    /**
     * منع الإضافة يدوياً (لأنه سجل تعديلات تاريخي يتم آلياً)
     */
    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    /**
     * منع التعديل اليدوي على السجل التاريخي
     */
    public function update(AuthUser $authUser, HighSchoolDegreeHistory $record): bool
    {
        return false;
    }

    /**
     * منع الحذف
     */
    public function delete(AuthUser $authUser, HighSchoolDegreeHistory $record): bool
    {
        return false;
    }
}
