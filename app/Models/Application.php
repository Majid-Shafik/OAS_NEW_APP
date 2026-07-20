<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositeKey;
use Awobaz\Compoships\Compoships;

class Application extends Model
{
    use HasCompositeKey, Compoships {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }

    protected $table = 'applications';
    protected $primaryKey = 'APPLICATION_IDENT';
    protected $compositeKeys = ['UNID', 'APPLICATION_IDENT'];
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'APPLICATION_IDENT', 'UNID', 'APPLICANT_IDENT', 'FACULTY_IDENT', 'PROGRAM_IDENT', 'OFFERING_IDENT', 'OFFER_GROUP_IDENT', 'STUDYTYPE_IDENT', 'CHOICE_NO', 'SEC_SCHOOL_RATE', 'F_ACCEPT', 'ACCEPTED', 'ENTRANCE_EXAM_AVERAGE', 'ENTRANCE_EXAM_WEIGHT', 'FINAL_MARK', 'CONFIRMED_BY_APPLICANT', 'CONFIRMED_ON', 'RECORDDATE', 'INSERTED_BY', 'APP_BILL_IDENT', 'PAYMENT_FLAG', 'STATUS', 'SHAW_APPLICANT_RESULTE', 'IMPORTED', 'STUDENT_CODE', 'EXPORTED'
    ];

    protected $casts = [
        'STATUS' => \App\Enums\ApplicationStatus::class,
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
}
