<?php

namespace App\Models;

use App\Traits\HasCompositeKey;
use App\Traits\HasUniversityScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use Compoships, HasCompositeKey {
        HasCompositeKey::setKeysForSaveQuery insteadof Compoships;
    }
    use HasUniversityScope;

    protected $table = 'faculty';

    protected $primaryKey = 'FACULTY_IDENT';

    protected $compositeKeys = ['UNID', 'FACULTY_IDENT'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'UNID', 'FACULTY_IDENT', 'NEW_ID', 'FACULTY_NAME', 'NEW_NAME', 'IS_IT_ENABLE', 'F_ACCEPT', 'F_ACCEPT_EXAM_IDENT', 'SHOW_CONFIRMED', 'ORDERING_MOD_ID', 'USE_LIMIT_APPS', 'ORDERBY', 'FACULTY_ORDER',
    ];

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function programs()
    {
        return $this->hasMany(Program::class, ['UNID', 'FACULTY_IDENT'], ['UNID', 'FACULTY_IDENT']);
    }
}
