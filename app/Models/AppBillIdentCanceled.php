<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppBillIdentCanceled extends Model
{
    protected $table = 'app_bill_ident_canceled';
    protected $primaryKey = 'IDENT';
    public $timestamps = false;

    protected $fillable = [
        'APP_BILL_IDENT',
        'PAYMENT',
        'BONDS_ID',
        'BONDS_DATE',
        'PAYMENT_FLAG',
        'PAY_METHOD_ID',
        'PAYMENT_BY',
        'ACTUAL_PAYMENT_DATE',
        'NOTE',
        'CANCELED_BY',
        'CANCELED_ON',
    ];

    public function canceledBy()
    {
        return $this->belongsTo(User::class, 'CANCELED_BY', 'USER_IDENT');
    }

    public function paymentBy()
    {
        return $this->belongsTo(User::class, 'PAYMENT_BY', 'USER_IDENT');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'APP_BILL_IDENT', 'APP_BILL_IDENT');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'PAY_METHOD_ID', 'PAY_METHOD_ID');
    }
}
