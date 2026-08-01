<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'applications';

    protected $primaryKey = 'APPLICATION_IDENT';

    protected $compositeKeys = ['UNID', 'APPLICATION_IDENT'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'APPLICATION_IDENT', 'UNID', 'APPLICANT_IDENT', 'FACULTY_IDENT', 'PROGRAM_IDENT', 'OFFERING_IDENT', 'OFFER_GROUP_IDENT', 'STUDYTYPE_IDENT', 'CHOICE_NO', 'SEC_SCHOOL_RATE', 'F_ACCEPT', 'ACCEPTED', 'ENTRANCE_EXAM_AVERAGE', 'ENTRANCE_EXAM_WEIGHT', 'FINAL_MARK', 'CONFIRMED_BY_APPLICANT', 'CONFIRMED_ON', 'RECORDDATE', 'INSERTED_BY', 'APP_BILL_IDENT', 'PAYMENT_FLAG', 'STATUS', 'SHAW_APPLICANT_RESULTE', 'IMPORTED', 'STUDENT_CODE', 'EXPORTED',
    ];

    protected $casts = [
        'STATUS' => ApplicationStatus::class,
        'PAYMENT_FLAG' => \App\Enums\PaymentMethodEnum::class,
    ];
 

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }

    public function program()
    {
        return $this->belongsTo(Program::class, ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT'], ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT']);
    }

    public function studyType()
    {
        return $this->belongsTo(StudyType::class, ['UNID', 'STUDYTYPE_IDENT'], ['UNID', 'STUDYTYPE_IDENT']);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'PAYMENT_FLAG', 'PAY_METHOD_ID');
    }

    public function applicationGroup()
    {
        return $this->belongsTo(ApplicationGroup::class, 'APP_BILL_IDENT', 'APP_BILL_IDENT');
    }

    public function offering()
    {
        return $this->belongsTo(Offering::class, 'OFFERING_IDENT', 'OFFERING_IDENT');
    }

    public function offeringGroup()
    {
        return $this->belongsTo(OfferingGroup::class, 'OFFER_GROUP_IDENT', 'OFFER_GROUP_IDENT');
    }

    public function insertedBy()
    {
        return $this->belongsTo(User::class, 'INSERTED_BY', 'USER_IDENT');
    }

    public function accept(): bool
    {
        return $this->update([
            'ACCEPTED' => 1,
            'STATUS' => ApplicationStatus::Accept,
        ]);
    }

    public function cancelAccept(): bool
    {
        $updated = $this->update([
            'ACCEPTED' => 0,
            'STATUS' => ApplicationStatus::New,
        ]);

        if ($this->applicant && $this->applicant->ADMITTED_OFFERING == $this->OFFERING_IDENT) {
            $this->applicant->update([
                'ADMITTED_FACULITY' => 0,
                'ADMITTED_PROGRAM' => 0,
                'ADMITTED_OFFERING' => 0,
                'ADMITTED_ON' => now(),
            ]);
        }

        return $updated;
    }

    public function confirmApplication(string $studentCode): bool
    {
        $updated = $this->update([
            'CONFIRMED_BY_APPLICANT' => 1,
            'STUDENT_CODE' => $studentCode,
            'CONFIRMED_ON' => now(),
        ]);

        if ($this->applicant) {
            $this->applicant->update([
                'ADMITTED_FACULITY' => $this->FACULTY_IDENT,
                'ADMITTED_PROGRAM' => $this->PROGRAM_IDENT,
                'ADMITTED_OFFERING' => $this->OFFERING_IDENT,
                'ADMITTED_ON' => now(),
            ]);
        }

        return $updated;
    }

    public function cancelConfirm(): bool
    {
        $updated = $this->update([
            'CONFIRMED_BY_APPLICANT' => 0,
            'STUDENT_CODE' => '0',
            'CONFIRMED_ON' => now(),
        ]);

        if ($this->applicant && $this->applicant->ADMITTED_OFFERING == $this->OFFERING_IDENT) {
            $this->applicant->update([
                'ADMITTED_FACULITY' => 0,
                'ADMITTED_PROGRAM' => 0,
                'ADMITTED_OFFERING' => 0,
                'ADMITTED_ON' => now(),
            ]);
        }

        return $updated;
    }

    protected static function booted()
    {
        static::creating(function ($application) {
            if (empty($application->APPLICATION_IDENT)) {
                // قفل سجل الجامعة لمنع التداخل (Race Condition) أثناء توليد المعرف
                \App\Models\University::where('UNID', $application->UNID)->lockForUpdate()->first();
                
                $maxIdent = static::where('UNID', $application->UNID)->max('APPLICATION_IDENT');
                $application->APPLICATION_IDENT = $maxIdent ? $maxIdent + 1 : 1;
            }
        });

        static::created(function ($application) {
            if ($application->applicant) {
                $application->applicant->update([
                    'STATUS' => \App\Enums\ApplicantStatus::Updated,
                    'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                ]);
            }
        });

        static::deleting(function ($application) {
            if (!empty($application->PAYMENT_FLAG) && $application->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE) {
                \Filament\Notifications\Notification::make()
                    ->title('لا يمكن حذف رغبة مسددة')
                    ->body('هذه الرغبة مرتبطة بحافظة سداد مالية ولا يمكن حذفها إلا بعد إلغاء السداد.')
                    ->danger()
                    ->send();

                return false;
            }
        });

        static::deleted(function ($application) {
            if ($application->applicant) {
                $application->applicant->update([
                    'STATUS' => \App\Enums\ApplicantStatus::Updated,
                    'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                ]);
            }

            $data = $application->toArray();
            $data['deleted_at'] = now();
            $data['deleted_by'] = auth()->id();
            
            \App\Models\DeletedApplication::insert($data);
        });
    }
}
