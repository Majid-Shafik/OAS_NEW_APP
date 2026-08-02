<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    protected $primaryKey = 'USER_IDENT';

    public $timestamps = false;

    protected $fillable = [
        'USER_NAME',
        'LOGON_ID',
        'LOGON_PASS',
        'EMAIL',
        'MOBILE_PHONE',
        'GENDER',
        'GROUP_IDENT',
        'PROGRAM_IDENT',
        'IS_IT_ENABLE',
        'remember_token',
    ];

    protected $casts = [
        'FIRST_TIME' => 'boolean',
        'IS_IT_ENABLE' => 'boolean',
        'RECORDDATE' => 'datetime',
    ];

    protected $hidden = [
        'LOGON_PASS',
        'remember_token',
    ];


    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin') || $this->hasRole('admin') ;
    }

    public function getAuthIdentifierName()
    {
        return 'USER_IDENT';
    }

    public function getAuthPasswordName()
    {
        return 'LOGON_PASS';
    }

    public function getAuthPassword()
    {
        return $this->LOGON_PASS;
    }


    protected static function booted(): void
    {
        static::creating(function ($record) {
            $record->INSERTED_BY = Auth::id();
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        Log::info('canAccessPanel called for user: ' . $this->USER_IDENT . ', IS_IT_ENABLE: ' . $this->IS_IT_ENABLE . ', DB: ' . config('database.connections.tenant.database'));

        // إذا كان رقم الجامعة غير معروف (لا هو 0 للمشرف، ولا يوجد له سجل في جدول الجامعات) نمنع الدخول
        if ($this->UNID != 0 && ! $this->university) {
            return false;
        }

        return $this->IS_IT_ENABLE == 1;
    }

    public function getFilamentName(): string
    {
        return (string) ($this->USER_NAME ?? $this->LOGON_ID ?? 'User');
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'UNID', 'UNID');
    }

    public function inserter()
    {
        return $this->belongsTo(User::class, 'INSERTED_BY', 'USER_IDENT');
    }
}
