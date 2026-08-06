<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\ApplicationGroup;
use App\Models\ApplicationsClearing;
use App\Models\Offering;
use App\Models\OfferingGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ApplicantRegistrationService
{
    /**
     * Register applications for a given applicant.
     *
     * @param Applicant $applicant
     * @param array<int> $offeringIdents Array of OFFERING_IDENT values
     * @param bool $isClearing
     * @param int $imported 2 for online, 1 for local app, etc (refer to old system conventions)
     * @return array Result containing status and message
     * @throws Exception
     */
    public function registerApplications(Applicant $applicant, array $offeringIdents, bool $isClearing = false, int $imported = 2)
    {
        try {
            DB::beginTransaction();

            $unid = $applicant->UNID;
            $applicantIdent = $applicant->APPLICANT_IDENT;
            $userId = auth()->id() ?? -1;

            // Load the offerings and their groups in advance to avoid N+1 queries
            $offerings = Offering::with('offeringGroup')
                ->where('UNID', $unid)
                ->whereIn('OFFERING_IDENT', $offeringIdents)
                ->get();

            if ($offerings->isEmpty()) {
                throw new Exception("لم يتم العثور على الرغبة المحددة أو أنها غير متاحة لهذه الجامعة.");
            }

            // Get current number of applications for choice number ordering
            $currentAppCount = Application::where('APPLICANT_IDENT', $applicantIdent)
                ->where('UNID', $unid)
                ->count();

            $successCount = 0;
            $failedOfferings = [];

            foreach ($offerings as $offering) {
                $offerGroup = $offering->offeringGroup;
                
                if (!$offerGroup) {
                    $failedOfferings[] = [
                        'offering' => $offering->OFFERING_IDENT,
                        'reason' => 'Missing Offer Group'
                    ];
                    continue;
                }

                $offerGroupIdent = $offerGroup->OFFER_GROUP_IDENT;

                // 1. Check if the applicant is allowed to apply to this offering
                try {
                    $this->checkIfAllowed($applicant, $offering);
                } catch (Exception $e) {
                    $failedOfferings[] = [
                        'offering' => $offering->OFFERING_IDENT,
                        'reason' => $e->getMessage()
                    ];
                    continue;
                }

                $currentAppCount++;

                // 2. Check if a bill (ApplicationGroup) already exists
                $appGroup = ApplicationGroup::where('UNID', $unid)
                    ->where('APPLICANT_IDENT', $applicantIdent)
                    ->where('OFFER_GROUP_IDENT', $offerGroupIdent)
                    ->first();

                $dateNow = now();

                // 3. If no bill, create one
                if (!$appGroup) {
                    $appGroup = ApplicationGroup::create([
                        'PAY_METHOD_ID' => 0,
                        'ACTUAL_PAYMENT_DATE' => $dateNow,
                        'APPLICANT_IDENT' => $applicantIdent,
                        'UNID' => $unid,
                        'FACULTY_IDENT' => $offering->FACULTY_IDENT,
                        'STUDYTYPE_IDENT' => $offering->STUDYTYPE_IDENT,
                        'OFFER_GROUP_IDENT' => $offerGroupIdent,
                        'ENABLE_PAYMENT' => $offerGroup->ENABLE_PAYMENT ?? 0,
                        'MOBILE_PHONE' => $applicant->MOBILE_PHONE ?: '000000000',
                        'EMAIL' => $applicant->EMAIL,
                        'APPLYING_COST' => $offerGroup->APPLYING_COST ?? 0,
                        'COST_TYPE' => $offerGroup->COST_TYPE ?? 0,
                        'APPS_COUNT' => 1,
                        'IS_ENABLE' => 0,
                        'IMPORTED' => $imported,
                        'PAYMENT' => 0,
                    ]);
                }

                // 4. Save the application (Choices)
                // Insert ignore logic equivalent in Eloquent (firstOrCreate or check existence)
                $application = Application::firstOrCreate(
                    [
                        'UNID' => $unid,
                        'APPLICANT_IDENT' => $applicantIdent,
                        'OFFERING_IDENT' => $offering->OFFERING_IDENT,
                    ],
                    [
                        'FACULTY_IDENT' => $offering->FACULTY_IDENT,
                        'PROGRAM_IDENT' => $offering->PROGRAM_IDENT,
                        'OFFER_GROUP_IDENT' => $offerGroupIdent,
                        'STUDYTYPE_IDENT' => $offering->STUDYTYPE_IDENT,
                        'CHOICE_NO' => $currentAppCount,
                        'SEC_SCHOOL_RATE' => $applicant->SEC_SCHOOL_RATE,
                        'ENTRANCE_EXAM_WEIGHT' => $offering->ENTRANCE_EXAM_WEIGHT ?? 0,
                        'RECORDDATE' => $dateNow,
                        'INSERTED_BY' => $userId,
                        'APP_BILL_IDENT' => $appGroup->APP_BILL_IDENT,
                        'PAYMENT_FLAG' => 0,
                        'STATUS' => 'NEW',
                        'IMPORTED' => $imported,
                    ]
                );

                if ($application->wasRecentlyCreated) {
                    // Application successfully added
                    $successCount++;
                    
                    // Update Applicant Status to UPDATED and FREEZE to UNFROZEN
                    $applicant->syncStatusAfterApplicationChange();

                    // 5. Update Applicant Group (Bill) Apps Count
                    $this->updateApplicantGroupNum($appGroup, $offerGroup);
                }
            }

            // 6. Save Clearing Data if Applicable
            if ($isClearing && $successCount > 0) {
                // Ensure clearing data logic goes here.
                // Assuming you have a function or data array to store.
                // You can expand this as needed.
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Successfully registered {$successCount} applications.",
                'failed' => $failedOfferings
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("ApplicantRegistrationService error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Checks if the applicant is allowed to apply for the specified offering.
     * Throws an exception if any rule is violated.
     *
     * @param Applicant $applicant
     * @param Offering $offering
     * @return void
     * @throws Exception
     */
    public function checkIfAllowed(Applicant $applicant, Offering $offering): void
    {
        $this->checkNotConfirmed($applicant);
        $this->checkLimitApp($applicant, $offering);
        $this->checkStandardApp($applicant, $offering);
        $this->checkRegisterDate($offering);
        $this->checkApproving($offering);
        $this->checkGroupPaid($applicant, $offering);
    }

    private function checkNotConfirmed(Applicant $applicant): void
    {
        $confirmedApp = Application::where('UNID', $applicant->UNID)
            ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
            ->where('CONFIRMED_BY_APPLICANT', 1)
            ->with('program')
            ->first();

        if ($confirmedApp || (!empty($applicant->ADMITTED_OFFERING) && $applicant->ADMITTED_OFFERING > 0)) {
            $progName = $confirmedApp?->program?->PROGRAM_NAME ?? '';
            throw new Exception("المتقدم مؤكد في تخصص" . ($progName ? " ({$progName})" : '') . "، يجب إلغاء تأكيده في ذلك التخصص أولاً حتى يستطيع إضافة رغبة جديدة.");
        }
    }

    private function checkLimitApp(Applicant $applicant, Offering $offering): void
    {
        $offerGroup = $offering->offeringGroup;
        if (!$offerGroup) throw new Exception("مجموعة الرغبة غير موجودة.");

        $maxNo = $offerGroup->MAX_CHOICE ?? 999;
        
        $applicationsNo = Application::where('UNID', $applicant->UNID)
            ->where('FACULTY_IDENT', $offerGroup->FACULTY_IDENT)
            ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
            ->where('STUDYTYPE_IDENT', $offerGroup->STUDYTYPE_IDENT)
            ->count();
            
        if ($applicationsNo >= $maxNo) {
            throw new Exception("لقد تجاوزت الحد الأعلى المسموح به للرغبات في هذه المجموعة ({$maxNo}).");
        }
    }

    private function checkStandardApp(Applicant $applicant, Offering $offering): void
    {
        $isYemeni = $applicant->YEMEN_NATIONAL == 1;
        $maxAgeColumn = $isYemeni ? 'Y_SEC_SCHOOL_MAX_AGE' : 'NY_SEC_SCHOOL_MAX_AGE';

        // Secondary School Type match
        if ($offering->SEC_SCHOOL_TYPE != 'الكل') {
            if (trim($applicant->SEC_SCHOOL_TYPE) != trim($offering->SEC_SCHOOL_TYPE)) {
                throw new Exception("نوع الثانوية للطالب (" . trim($applicant->SEC_SCHOOL_TYPE) . ") لا يتطابق مع نوع الثانوية المطلوب للرغبة (" . trim($offering->SEC_SCHOOL_TYPE) . ").");
            }
        } else {
            // Check double program parameter logic
            $duplicateCount = Offering::where('UNID', $offering->UNID)
                ->where('PROGRAM_IDENT', $offering->PROGRAM_IDENT)
                ->where('STUDYTYPE_IDENT', $offering->STUDYTYPE_IDENT)
                ->where('OFFERING_IDENT', '<>', $offering->OFFERING_IDENT)
                ->where('SEC_SCHOOL_TYPE', $applicant->SEC_SCHOOL_TYPE)
                ->whereDate('FROM_DATE', '<=', now())
                ->whereDate('TO_DATE', '>=', now())
                ->count();
            if ($duplicateCount > 0) {
                throw new Exception("يوجد تخصص آخر متاح لنفس نوع الثانوية.");
            }
        }

        // Check Rate
        if (!($applicant->SEC_SCHOOL_RATE >= $offering->SEC_SCHOOL_ACCEPT_RATE)) {
            throw new Exception("معدل الثانوية للطالب ({$applicant->SEC_SCHOOL_RATE}%) أقل من الحد الأدنى المطلوب للقبول ({$offering->SEC_SCHOOL_ACCEPT_RATE}%).");
        }

        // Check Expire Date (Age of certificate)
        $maxAge = $offering->$maxAgeColumn ?? 10;
        $tillYear = date('Y') - $maxAge;
        if (intval($applicant->SEC_SCHOOL_YEAR) < $tillYear) {
            throw new Exception("سنة تخرج الطالب ({$applicant->SEC_SCHOOL_YEAR}) تتجاوز أقصى عمر مسموح به لشهادة الثانوية ({$maxAge} سنوات).");
        }

        // GS_MULTI_SYSTEMS_ONE_OFFER
        $university = $applicant->university;
        if ($university && $university->GS_MULTI_SYSTEMS_ONE_OFFER == 0) {
            $countSameProg = Application::where('UNID', $applicant->UNID)
                ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                ->where('PROGRAM_IDENT', $offering->PROGRAM_IDENT)
                ->count();
            if ($countSameProg > 0) {
                throw new Exception("لا يسمح بتسجيل نفس التخصص في أكثر من نظام دراسي.");
            }
        }

        // GS_MULTI_SYSTEMS
        if ($university && $university->GS_MULTI_SYSTEMS == 0) {
            $countDiffSystem = Application::where('UNID', $applicant->UNID)
                ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                ->where('STUDYTYPE_IDENT', '<>', $offering->STUDYTYPE_IDENT)
                ->count();
            if ($countDiffSystem > 0) {
                throw new Exception("يجب أن تكون الرغبات في نفس النظام الدراسي الذي تم اختياره أولاً.");
            }
        }

        // GS_NY_GENERAL_SYSTEM
        if ($university && $university->GS_NY_GENERAL_SYSTEM == 0 && $offering->STUDYTYPE_IDENT == 1 && !$isYemeni) {
            throw new Exception("لا يسمح بتنسيق الطلاب غير اليمنيين في النظام العام.");
        }
    }

    private function checkRegisterDate(Offering $offering): void
    {
        $today = now()->format('Y-m-d');
        if ($offering->FROM_DATE && $offering->TO_DATE) {
            if ($today > $offering->TO_DATE || $today < $offering->FROM_DATE) {
                throw new Exception("تاريخ التنسيق لهذه الرغبة منتهي أو لم يبدأ بعد.");
            }
        }
    }

    private function checkApproving(Offering $offering): void
    {
        if ($offering->APPROVAL != 1) {
            throw new Exception("هذه الرغبة غير معتمدة حالياً.");
        }
    }

    private function checkGroupPaid(Applicant $applicant, Offering $offering): void
    {
        $appGroup = ApplicationGroup::where('UNID', $offering->UNID)
            ->where('OFFER_GROUP_IDENT', $offering->OFFER_GROUP_IDENT)
            ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
            ->first();

        if ($appGroup && $appGroup->PAYMENT_FLAG > 0) {
            throw new Exception("لقد تم السداد لهذه المجموعة مسبقاً، لا يمكن إضافة رغبات جديدة.");
        }
    }

    /**
     * Updates the APPS_COUNT and IS_ENABLE status for an ApplicationGroup.
     * Equivalent to `UpdateApplicantGroupNum` in legacy system.
     *
     * @param ApplicationGroup $appGroup
     * @param OfferingGroup $offerGroup
     * @return void
     */
    private function updateApplicantGroupNum(ApplicationGroup $appGroup, OfferingGroup $offerGroup)
    {
        $minApp = $offerGroup->MIN_CHOICE ?? 0;
        $maxApp = $offerGroup->MAX_CHOICE ?? 999;

        $appsCount = Application::where('APP_BILL_IDENT', $appGroup->APP_BILL_IDENT)->count();

        $isEnable = 0;
        if ($appsCount >= $minApp && $appsCount <= $maxApp && $appsCount > 0) {
            $isEnable = 1;
        }

        $appGroup->update([
            'APPS_COUNT' => $appsCount,
            'IS_ENABLE' => $isEnable,
        ]);
    }
}
