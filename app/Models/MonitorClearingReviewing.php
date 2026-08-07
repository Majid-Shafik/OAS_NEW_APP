<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class MonitorClearingReviewing extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'monitor_clearing_reviwing';

    protected $primaryKey = 'APPLICANT_IDENT';

    protected $compositeKeys = ['UNID', 'APPLICANT_IDENT', 'RECORD_DATE']; // Including RECORD_DATE because it's a history table

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'UNID',
        'APPLICANT_IDENT',
        'REVIEW_RESULTE',
        'REJECT_REASON',
        'REVIEW_BY',
        'RECORD_DATE',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }
    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'REVIEW_BY', 'USER_IDENT');
    }


}
