<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_method';

    protected $primaryKey = 'PAY_METHOD_ID';

    public $timestamps = false;

    protected $fillable = [
        'PAY_METHOD_ID', 'PAY_METHOD', 'IS_ENABLED',
    ];
}
