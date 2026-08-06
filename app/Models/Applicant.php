<?php

namespace App\Models;

use App\Enums\ApplicantStatus;
use App\Enums\Gender;
use App\Models\University;
use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'applicant';

    protected $primaryKey = 'APPLICANT_IDENT';

    protected $compositeKeys = ['UNID', 'APPLICANT_IDENT'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'UNID', 'APPLICANT_IDENT', 'NATIONAL_NUMBER', 'FIRST_NAME', 'LAST_NAME', 'FULL_NAME', 'PLACE_OF_BIRTH', 'DATE_OF_BIRTH', 'PROVINCE', 'TERRITORY', 'COUNTRY_IDENT', 'COUNTRY_NAME', 'IDENT_TYPE', 'IDENT_NO', 'YEMEN_NATIONAL', 'SEC_SCHOOL_TYPE', 'SEC_SCHOOL_PLACE', 'SEC_SCHOOL_PROVINCE', 'SEC_SCHOOL_TERRITORY', 'SEC_SCHOOL_YEAR', 'SEC_SCHOOL_NAME', 'SEC_SCHOOL_RATE', 'SEC_SCHOOL_SEATNO', 'SEC_SCHOOL_MARK', 'SEC_SCHOOL_OVERALLMARK', 'ADMITTED_OFFERING', 'ADMITTED_PROGRAM', 'ADMITTED_FACULITY', 'ADMITTED_ON', 'EMAIL', 'MOBILE_PHONE', 'BLOOD_GROUP', 'GENDER', 'NOTE', 'RECORDDATE', 'STATUS', 'FREEZE', 'INSERTED_BY', 'LAST_UPDATED_BY', 'LAST_UPDATED_ON', 'APPROVED_BY', 'APPROVED_ON', 'IMPORTED', 'APPLICANT_TYPE', 'IS_CLEARING', 'REVIEWED', 'REVIEW_BY', 'REVIEW_ON', 'REJECT_REASON', 'SECOND_REVIEWED', 'SECOND_REVIEWED_BY', 'SECOND_REVIEWED_ON', 'SECOND_REJECT_REASON', 'EXPORTED',
    ];

    protected $casts = [
        'STATUS' => ApplicantStatus::class,
        'IS_CLEARING' => \App\Enums\IsClearingType::class,
        'FREEZE' => \App\Enums\FreezeStatus::class,
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'ADMITTED_FACULITY'], ['UNID', 'FACULTY_IDENT']);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, ['UNID', 'ADMITTED_FACULITY', 'ADMITTED_PROGRAM'], ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT']);
    }

    public function applications()
    {
        return $this->hasMany(Application::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function getProfileUrl(): string
    {
        $isClearing = $this->IS_CLEARING;
        if ($isClearing instanceof \App\Enums\IsClearingType) {
            $isClearing = $isClearing->value;
        }

        if ($isClearing === 1 || $isClearing === \App\Enums\IsClearingType::CLEARING->value) {
            return \App\Filament\Resources\ClearingApplicants\ClearingApplicantResource::getUrl('view', ['record' => $this]);
        }

        return \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $this]);
    }

    public function applicationGroups()
    {
        return $this->hasMany(ApplicationGroup::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function applicantAttachments()
    {
        return $this->hasMany(ApplicantAttachment::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function applicationsClearing()
    {
        return $this->hasOne(ApplicationsClearing::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function monitorClearingReviewing()
    {
        return $this->hasMany(MonitorClearingReviewing::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function insertedBy()
    {
        return $this->belongsTo(User::class, 'INSERTED_BY', 'USER_IDENT');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'LAST_UPDATED_BY', 'USER_IDENT');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'APPROVED_BY', 'USER_IDENT');
    }

    public function reviewBy()
    {
        return $this->belongsTo(User::class, 'REVIEW_BY', 'USER_IDENT');
    }

    public function secondReviewedBy()
    {
        return $this->belongsTo(User::class, 'SECOND_REVIEWED_BY', 'USER_IDENT');
    }

    protected static function booted()
    {
        static::creating(function ($applicant) {
            $applicant->INSERTED_BY = $applicant->INSERTED_BY ?? (auth()->id() ?? -1);
            $applicant->RECORDDATE = $applicant->RECORDDATE ?? now();
            $applicant->IMPORTED = $applicant->IMPORTED ?? 2;
            $applicant->APPLICANT_TYPE = $applicant->APPLICANT_TYPE ?? 1;
            $applicant->YEMEN_NATIONAL = $applicant->YEMEN_NATIONAL ?? 1;
            
            if (empty($applicant->APPLICANT_IDENT)) {
                // قفل سجل الجامعة لمنع التداخل (Race Condition) أثناء توليد المعرف
                University::where('UNID', $applicant->UNID)->lockForUpdate()->first();
                
                $maxIdent = \Illuminate\Support\Facades\DB::table('applicant')
                    ->where('UNID', $applicant->UNID)
                    ->max('APPLICANT_IDENT');
                    
                $applicant->APPLICANT_IDENT = $maxIdent ? $maxIdent + 1 : 1;
            }
        });

        static::updating(function ($applicant) {
            $applicant->LAST_UPDATED_BY = auth()->id() ?? -1;
            $applicant->LAST_UPDATED_ON = now();
        });
    }

    /**
     * تحديث حالة المتقدم إلى تحت التعديل (UPDATED) وفك التجميد (UNFROZEN)
     * دالة موحدة يتم استدعاؤها عند إضافة أو حذف أي رغبة تقديم.
     *
     * @param int|string $unid
     * @param int|string $applicantIdent
     * @return void
     */
    public static function handleApplicationChanged(int|string $unid, int|string $applicantIdent): void
    {
        static::withoutGlobalScopes()
            ->where('UNID', $unid)
            ->where('APPLICANT_IDENT', $applicantIdent)
            ->update([
                'STATUS' => \App\Enums\ApplicantStatus::Updated->value,
                'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN->value,
                'LAST_UPDATED_BY' => auth()->id() ?? -1,
                'LAST_UPDATED_ON' => now(),
            ]);
    }

    /**
     * دالة مثيلة لتحديث حالة هذا المتقدم وفك تجميده
     */
    public function syncStatusAfterApplicationChange(): void
    {
        self::handleApplicationChanged($this->UNID, $this->APPLICANT_IDENT);

        $this->STATUS = \App\Enums\ApplicantStatus::Updated;
        $this->FREEZE = \App\Enums\FreezeStatus::UNFROZEN;
    }
}
