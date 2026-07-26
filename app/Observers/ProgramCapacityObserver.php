<?php

namespace App\Observers;

use App\Models\ProgramCapacity;
use App\Models\ProgramCapacityHistory;
use Illuminate\Support\Facades\Auth;

class ProgramCapacityObserver
{
    /**
     * Handle the ProgramCapacity "created" event.
     */
    public function created(ProgramCapacity $programCapacity): void
    {
        ProgramCapacityHistory::create([
            'UNID' => $programCapacity->UNID,
            'FACULTY_IDENT' => $programCapacity->program->FACULTY_IDENT ?? 0, // Fallback if not available
            'PROGRAM_IDENT' => $programCapacity->PROGRAM_IDENT,
            'STUDYTYPE_IDENT' => $programCapacity->STUDYTYPE_IDENT,
            'OLD_ENROLLMENT_CAPACITY' => 0,
            'NEW_ENROLLMENT_CAPACITY' => $programCapacity->ENROLLMENT_CAPACITY,
            'UPDATED_BY' => Auth::id() ?? 1,
            'NOTES' => 'تم إضافة طاقة استيعابية جديدة',
        ]);
    }

    /**
     * Handle the ProgramCapacity "updated" event.
     */
    public function updated(ProgramCapacity $programCapacity): void
    {
        if ($programCapacity->isDirty('ENROLLMENT_CAPACITY')) {
            ProgramCapacityHistory::create([
                'UNID' => $programCapacity->UNID,
                'FACULTY_IDENT' => $programCapacity->program->FACULTY_IDENT ?? 0,
                'PROGRAM_IDENT' => $programCapacity->PROGRAM_IDENT,
                'STUDYTYPE_IDENT' => $programCapacity->STUDYTYPE_IDENT,
                'OLD_ENROLLMENT_CAPACITY' => $programCapacity->getOriginal('ENROLLMENT_CAPACITY') ?? 0,
                'NEW_ENROLLMENT_CAPACITY' => $programCapacity->ENROLLMENT_CAPACITY,
                'UPDATED_BY' => Auth::id() ?? 1,
                'NOTES' => 'تم تعديل الطاقة الاستيعابية',
            ]);
        }
    }
}
