<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramCapacityHistory extends Model
{
    use HasFactory, Compoships, HasUniversityScope;

    protected $table = 'programs_capacity_history';

    protected $primaryKey = 'PC_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'UNID',
        'FACULTY_IDENT',
        'PROGRAM_IDENT',
        'STUDYTYPE_IDENT',
        'OLD_ENROLLMENT_CAPACITY',
        'NEW_ENROLLMENT_CAPACITY',
        'UPDATED_BY',
        'NOTES',
        'UPDATED_ON',
    ];

    public function programCapacity()
    {
        return $this->belongsTo(ProgramCapacity::class, ['UNID', 'PROGRAM_IDENT', 'STUDYTYPE_IDENT'], ['UNID', 'PROGRAM_IDENT', 'STUDYTYPE_IDENT']);
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, ['UNID', 'PROGRAM_IDENT'], ['UNID', 'PROGRAM_IDENT']);
    }

    public function studyType()
    {
        return $this->belongsTo(StudyType::class, 'STUDYTYPE_IDENT', 'STUDYTYPE_IDENT');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'UPDATED_BY', 'USER_IDENT'); // Assuming User model has USER_IDENT as primary key or something, I need to check User model.
    }
}
