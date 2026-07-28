<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class ApplicantAttachment extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'applicant_attachments';

    protected $primaryKey = 'APPLICANT_IDENT'; // No single primary key, but for HasCompositeKey we specify a dummy or just rely on compositeKeys

    protected $compositeKeys = ['UNID', 'APPLICANT_IDENT', 'ATTACH_IDENT'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'UNID', 'APPLICANT_IDENT', 'ATTACH_IDENT'
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, ['UNID', 'APPLICANT_IDENT'], ['UNID', 'APPLICANT_IDENT']);
    }
}
