<?php

namespace App\Models;

use App\Helpers\PortalHelper;
use App\Traits\HasUniversityScope;
use Illuminate\Database\Eloquent\Model;

class HighSchoolDegreeBType extends Model
{
    use HasUniversityScope;

    protected $table = 'high_school_degrees_b_type';

    protected $primaryKey = 'SS_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'UNID',
        'SEC_SCHOOL_SEATNO',
        'SEC_SCHOOL_YEAR',
        'STUDENT_NAME',
        'SEC_SCHOOL_MARK',
        'SEC_SCHOOL_OVERALLMARK',
        'FINAL_STATUS',
        'SEC_SCHOOL_RATE',
        'SEC_SCHOOL_PROVINCE',
        'SEC_SCHOOL_TERRITORY',
        'SEC_SCHOOL_TYPE',
        'SEC_SCHOOL_NAME',
        'SEC_SCHOOL_PLACE',
        'PLACE_OF_BIRTH',
        'DATE_OF_BIRTH',
        'PROVINCE',
        'TERRITORY',
        'COUNTRY_IDENT',
        'COUNTRY_NAME',
        'YEMEN_NATIONAL',
        'GENDER',
        'MOBILE_PHONE',
        'EMAIL',
        'RECORDDATE',
        'INSERTED_BY',
        'APPROVED',
        'APPROVED_BY',
        'APPROVED_ON',
        'REJECT_REASON',
        'SEC_SCHOOL_CERTIFICATE'
    ];

    protected $casts = [
        'DATE_OF_BIRTH' => 'date',
        'APPROVED_ON' => 'datetime',
        'RECORDDATE' => 'datetime',
        'YEMEN_NATIONAL' => 'boolean',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function insertedBy()
    {
        return $this->belongsTo(User::class, 'INSERTED_BY', 'USER_IDENT');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'APPROVED_BY', 'USER_IDENT');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'COUNTRY_IDENT', 'COUNTRY_IDENT');
    }

    protected static function booted()
    {
        static::updated(function ($model) {
            if ($model->isDirty('SEC_SCHOOL_CERTIFICATE')) {
                $oldBasename = $model->getOriginal('SEC_SCHOOL_CERTIFICATE');
                if ($oldBasename) {
                    $portalYear =  PortalHelper::getActiveYear();
                    $path = "uploads/p{$portalYear}/images/attachments/secondary/{$oldBasename}.jpg";
                    \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->delete($path);
                }
            }
        });

        static::deleted(function ($model) {
            if ($model->SEC_SCHOOL_CERTIFICATE) {
                $portalYear =  PortalHelper::getActiveYear();
                $path = "uploads/p{$portalYear}/images/attachments/secondary/{$model->SEC_SCHOOL_CERTIFICATE}.jpg";
                \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->delete($path);
            }
        });
    }
}
