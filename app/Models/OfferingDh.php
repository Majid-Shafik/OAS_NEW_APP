<?php

namespace App\Models;

use App\Enums\ActionType;
use App\Traits\HasUniversityScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferingDh extends Model
{
    use \Awobaz\Compoships\Compoships, HasFactory;
    use HasUniversityScope;

    protected $table = 'offerings_dh';

    protected $primaryKey = 'REVESION';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'ACTION' => ActionType::class,
    ];

    public function actionUser()
    {
        return $this->belongsTo(User::class, 'USER', 'USER_IDENT');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'RECORD_BY', 'USER_IDENT');
    }

    public function lastUpdatedBy()
    {
        return $this->belongsTo(User::class, 'LAST_UPDATED_BY', 'USER_IDENT');
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
}
