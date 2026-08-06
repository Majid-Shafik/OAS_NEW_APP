<?php

namespace App\Models;

use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\OfferingObserver;

#[ObservedBy([OfferingObserver::class])]
class Offering extends Model
{
    use Compoships, HasFactory, HasUniversityScope;

    protected $table = 'offerings';

    protected $primaryKey = 'OFFERING_IDENT';

    public $timestamps = false;

    protected $guarded = [];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'RECORD_BY', 'USER_IDENT');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'LAST_UPDATED_BY', 'USER_IDENT');
    }

    public function offeringDhs()
    {
        return $this->hasMany(OfferingDh::class, 'OFFERING_IDENT', 'OFFERING_IDENT');
    }

    public function approvalBy()
    {
        return $this->belongsTo(User::class, 'APPROVAL_BY', 'USER_IDENT');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
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
        return $this->belongsTo(StudyType::class, 'STUDYTYPE_IDENT', 'STUDYTYPE_IDENT');
    }

    public function offeringGroup()
    {
        return $this->belongsTo(OfferingGroup::class, 'OFFER_GROUP_IDENT', 'OFFER_GROUP_IDENT');
    }

    public function requestAdjustOfferings()
    {
        return $this->hasMany(RequestAdjustOffering::class, 'OFFERING_IDENT', 'OFFERING_IDENT');
    }
}
