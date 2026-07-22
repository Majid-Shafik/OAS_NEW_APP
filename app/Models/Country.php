<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $primaryKey = 'COUNTRY_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'COUNTRY_IDENT', 'CODE', 'COUNTRY_ENG_NAME', 'COUNTRY_NAME', 'FULL_NAME', 'ISO3', 'NUMBER', 'CONTINENT_CODE', 'DISPLAY_ORDER',
    ];
}
