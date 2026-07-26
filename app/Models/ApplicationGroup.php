<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositeKey;
use Awobaz\Compoships\Compoships;

class ApplicationGroup extends Model
{
    use Compoships, HasFactory, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }

    protected $table = 'applications_group';

    protected $primaryKey = 'APP_BILL_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'APP_BILL_IDENT',
        'PAY_METHOD_ID',
        'ACTUAL_PAYMENT_DATE',
        'APPLICANT_IDENT',
        'UNID',
        'FACULTY_IDENT',
        'STUDYTYPE_IDENT',
        'OFFER_GROUP_IDENT',
        'ENABLE_PAYMENT',
        'MOBILE_PHONE',
        'EMAIL',
        'APPLYING_COST',
        'COST_TYPE',
        'APPS_COUNT',
        'IS_ENABLE',
        'IMPORTED',
        'PAYMENT',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }

    public function offerGroup()
    {
        return $this->belongsTo(OfferingGroup::class, 'OFFER_GROUP_IDENT', 'OFFER_GROUP_IDENT');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'APP_BILL_IDENT', 'APP_BILL_IDENT');
    }
}
