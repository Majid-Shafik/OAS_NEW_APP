<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class ApplicationsClearing extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'applications_clearing';

    protected $primaryKey = 'APPLICANT_IDENT';

    protected $compositeKeys = ['UNID', 'APPLICANT_IDENT'];

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'UNID',
        'APPLICANT_IDENT',
        'FROM_COUNTRY_IDENT',
        'FROM_COUNTRY_NAME',
        'FROM_UNIV_IDENT',
        'FROM_UNIV_NAME',
        'FROM_FACULTY_IDENT',
        'FROM_FACULTY_NAME',
        'FROM_PROGRAM_IDENT',
        'FROM_PROGRAM_NAME',
        'NO_STUDY_YEARS',
        'STUDY_LEVEL',
        'FROM_YEAR',
        'MOVING_REASON',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }
}
