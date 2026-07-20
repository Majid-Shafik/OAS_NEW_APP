<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;

class Offering extends Model
{
    use HasFactory, Compoships, HasUniversityScope;

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
}
