<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class System extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'english_name',
        'slug',
        'description',
        'url',
        'thumbnail',
        'icon',
        'color',
        'display_order',
        'is_active',
        'open_in_new_tab',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
    ];
}
