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

    protected static function booted()
    {
        static::saving(function ($clearing) {
            if ($clearing->FROM_COUNTRY_IDENT && empty($clearing->FROM_COUNTRY_NAME)) {
                $clearing->FROM_COUNTRY_NAME = \App\Models\Country::withoutGlobalScopes()->where('COUNTRY_IDENT', $clearing->FROM_COUNTRY_IDENT)->value('COUNTRY_NAME');
            }
            if ($clearing->FROM_UNIV_IDENT && empty($clearing->FROM_UNIV_NAME)) {
                $clearing->FROM_UNIV_NAME = \App\Models\University::withoutGlobalScopes()->where('UNID', $clearing->FROM_UNIV_IDENT)->value('U_NAME');
            }
            if ($clearing->FROM_FACULTY_IDENT && empty($clearing->FROM_FACULTY_NAME)) {
                $clearing->FROM_FACULTY_NAME = \App\Models\Faculty::withoutGlobalScopes()
                    ->where('UNID', $clearing->FROM_UNIV_IDENT)
                    ->where('FACULTY_IDENT', $clearing->FROM_FACULTY_IDENT)
                    ->value('FACULTY_NAME');
            }
            if ($clearing->FROM_PROGRAM_IDENT && empty($clearing->FROM_PROGRAM_NAME)) {
                $clearing->FROM_PROGRAM_NAME = \App\Models\Program::withoutGlobalScopes()
                    ->where('UNID', $clearing->FROM_UNIV_IDENT)
                    ->where('FACULTY_IDENT', $clearing->FROM_FACULTY_IDENT)
                    ->where('PROGRAM_IDENT', $clearing->FROM_PROGRAM_IDENT)
                    ->value('PROGRAM_NAME');
            }
        });
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function clearingAttachments()
    {
        return $this->hasMany(ApplicantAttachment::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT'])
                    ->whereIn('ATTACH_IDENT', [3, 4, 5]);
    }

    public function gradesAttachment()
    {
        return $this->hasOne(ApplicantAttachment::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT'])
                    ->where('ATTACH_IDENT', 3);
    }

    public function clearingFormAttachment()
    {
        return $this->hasOne(ApplicantAttachment::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT'])
                    ->where('ATTACH_IDENT', 4);
    }

    public function exceptionAttachment()
    {
        return $this->hasOne(ApplicantAttachment::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT'])
                    ->where('ATTACH_IDENT', 5);
    }
}
