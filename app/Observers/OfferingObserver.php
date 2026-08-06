<?php

namespace App\Observers;

use App\Models\Offering;
use App\Models\OfferingDh;
use App\Enums\ActionType;
use Illuminate\Support\Facades\Auth;

class OfferingObserver
{
    /**
     * Handle the Offering "created" event.
     */
    public function created(Offering $offering): void
    {
        $this->logAction($offering, ActionType::INSERT);
    }

    /**
     * Handle the Offering "updated" event.
     */
    public function updated(Offering $offering): void
    {
        $this->logAction($offering, ActionType::UPDATE);
    }

    /**
     * Handle the Offering "deleted" event.
     */
    public function deleted(Offering $offering): void
    {
        $this->logAction($offering, ActionType::DELETE);
    }

    /**
     * Log the action to offerings_dh table.
     */
    protected function logAction(Offering $offering, ActionType $action): void
    {
        OfferingDh::create([
            'ACTION' => $action,
            'USER' => Auth::id() ?? (Auth::user()?->USER_IDENT ?? '-1'),
            'ATIME' => now(),
            'OFFERING_IDENT' => $offering->OFFERING_IDENT,
            'UNID' => $offering->UNID,
            'OFFER_GROUP_IDENT' => $offering->OFFER_GROUP_IDENT,
            'FACULTY_IDENT' => $offering->FACULTY_IDENT,
            'PROGRAM_IDENT' => $offering->PROGRAM_IDENT,
            'STUDYTYPE_IDENT' => $offering->STUDYTYPE_IDENT,
            'SEC_SCHOOL_TYPE' => $offering->SEC_SCHOOL_TYPE,
            'SEC_SCHOOL_ACCEPT_RATE' => $offering->SEC_SCHOOL_ACCEPT_RATE,
            'ENTRANCE_EXAM_WEIGHT' => $offering->ENTRANCE_EXAM_WEIGHT,
            'Y_SEC_SCHOOL_MAX_AGE' => $offering->Y_SEC_SCHOOL_MAX_AGE,
            'NY_SEC_SCHOOL_MAX_AGE' => $offering->NY_SEC_SCHOOL_MAX_AGE,
            'ENTRANCE_EXAM_REQUIRED' => $offering->ENTRANCE_EXAM_REQUIRED,
            'FROM_DATE' => $offering->FROM_DATE,
            'TO_DATE' => $offering->TO_DATE,
            'SHOW_ALL_APPLICANTS' => $offering->SHOW_ALL_APPLICANTS,
            'DIRCT_RIGESTER' => $offering->DIRCT_RIGESTER,
            'RECORD_ON' => $offering->RECORD_ON,
            'RECORD_BY' => $offering->RECORD_BY,
            'LAST_UPDATED_ON' => $offering->LAST_UPDATED_ON,
            'LAST_UPDATED_BY' => $offering->LAST_UPDATED_BY,
            'APPROVAL' => $offering->APPROVAL,
            'APPROVAL_BY' => $offering->APPROVAL_BY,
            'APPROVAL_ON' => $offering->APPROVAL_ON,
            'APPROVAL_REGECT_REASON' => $offering->APPROVAL_REGECT_REASON,
        ]);
    }
}
