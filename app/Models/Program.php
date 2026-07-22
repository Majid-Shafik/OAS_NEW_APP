<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'programs';

    protected $primaryKey = 'PROGRAM_IDENT';

    protected $compositeKeys = ['UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'UNID', 'FACULTY_IDENT', 'PROGRAM_IDENT', 'NEW_ID', 'PROGRAM_NAME', 'NEW_NAME', 'PROGRAM_CLASS_ID', 'PROGRAM_LEVEL_ID', 'IS_IT_ENABLE',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }
}
