<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class GeneralStandard extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use \App\Traits\HasUniversityScope;

    protected $table = 'general_standards';

    protected $compositeKeys = ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT'];
    
    protected $primaryKey = 'PROGRAM_IDENT';

    public $incrementing = false;

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

    public function program()
    {
        return $this->belongsTo(Program::class, ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT'], ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT']);
    }
}
