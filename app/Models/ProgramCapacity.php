<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCapacity extends Model
{
    use HasFactory, Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'programs_capacity';

    public $timestamps = false;
    
    // There isn't typically a single primary key for this. It's composite.
    protected $primaryKey = 'PROGRAM_IDENT';
    protected $compositeKeys = ['UNID', 'PROGRAM_IDENT', 'STUDYTYPE_IDENT'];
    public $incrementing = false;

    protected $fillable = [
        'UNID',
        'PROGRAM_IDENT',
        'STUDYTYPE_IDENT',
        'ENROLLMENT_CAPACITY',
        'NOTE',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function program()
    {
        // Program has UNID, FACULTY_IDENT, PROGRAM_IDENT typically, so this might just link basically.
        return $this->belongsTo(Program::class, ['UNID', 'PROGRAM_IDENT'], ['UNID', 'PROGRAM_IDENT']);
    }

    public function studyType()
    {
        return $this->belongsTo(StudyType::class, 'STUDYTYPE_IDENT', 'STUDYTYPE_IDENT');
    }

    public function histories()
    {
        return $this->hasMany(ProgramCapacityHistory::class, ['UNID', 'PROGRAM_IDENT', 'STUDYTYPE_IDENT'], ['UNID', 'PROGRAM_IDENT', 'STUDYTYPE_IDENT']);
    }
}
