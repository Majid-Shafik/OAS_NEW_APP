<?php

namespace App\Models;

use App\Enums\ApplicantStatus;
use App\Enums\Gender;
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
        'GENDER' => Gender::class,
        'STATUS' => ApplicantStatus::class,
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
}
