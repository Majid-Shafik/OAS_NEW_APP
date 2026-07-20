<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'province';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    
    protected $fillable = [
        'NAME', 'ENG_NAME'
    ];
}
