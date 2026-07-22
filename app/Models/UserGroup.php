<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    protected $table = 'user_group';

    protected $primaryKey = 'GROUP_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'GROUP_IDENT', 'GROUP_NAME', 'NOTE', 'IS_IT_ENABLE', 'EDITABLE',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'GROUP_IDENT', 'GROUP_IDENT');
    }
}
