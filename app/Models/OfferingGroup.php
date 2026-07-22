<?php

namespace App\Models;

use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferingGroup extends Model
{
    use Compoships, HasFactory, HasUniversityScope;

    protected $table = 'offerings_groups';

    protected $primaryKey = 'OFFER_GROUP_IDENT';

    public $timestamps = false;

    protected $guarded = [];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }

    public function studyType()
    {
        return $this->belongsTo(StudyType::class, 'STUDYTYPE_IDENT', 'STUDYTYPE_IDENT');
    }

    public function offerings()
    {
        return $this->hasMany(Offering::class, 'OFFER_GROUP_IDENT', 'OFFER_GROUP_IDENT');
    }
}
