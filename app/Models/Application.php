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

    

    public function insertedBy()
    {
        return $this->belongsTo(User::class, 'INSERTED_BY', 'USER_IDENT');
    }

    protected static function booted()
    {
        static::deleted(function ($application) {
            $data = $application->toArray();
            $data['deleted_at'] = now();
            $data['deleted_by'] = auth()->id();
            
            \App\Models\DeletedApplication::insert($data);
        });
    }
}
