<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositeKey;
use Awobaz\Compoships\Compoships;

class StudyType extends Model
{
    use HasCompositeKey, Compoships {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }

    protected $table = 'study_type';
    protected $primaryKey = 'STUDYTYPE_IDENT';
    protected $compositeKeys = ['UNID', 'STUDYTYPE_IDENT'];
    public $incrementing = false;
    public $timestamps = false;
    
    protected $fillable = [
        'UNID', 'STUDYTYPE_IDENT', 'STUDYTYPE_NAME', 'NEW_ID'
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }
}
