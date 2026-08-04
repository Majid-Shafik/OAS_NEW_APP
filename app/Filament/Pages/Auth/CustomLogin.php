<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.LOGON_ID' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        \Illuminate\Support\Facades\Log::info('--- LOGIN ATTEMPT START ---', [
            'selected_database' => $data['database'] ?? 'none',
            'LOGON_ID' => $data['LOGON_ID'] ?? 'none',
        ]);

        // إعداد الاتصال بقاعدة بيانات العام المختار
        $credentials = $this->getCredentialsFromFormData($data);

        \Illuminate\Support\Facades\Log::info('Database connection configured', [
            'tenant_db' => config('database.connections.tenant.database'),
            'mysql_db' => config('database.connections.mysql.database'),
            'default_conn' => DB::getDefaultConnection(),
        ]);

        // جلب المستخدم والتحقق من كلمة المرور عبر الـ Provider (يدعم Bcrypt و القديم بأمان)
        $user = User::where('LOGON_ID', $data['LOGON_ID'])->first();

        \Illuminate\Support\Facades\Log::info('User lookup result', [
            'user_found' => (bool) $user,
            'user_ident' => $user?->USER_IDENT,
            'is_it_enable' => $user?->IS_IT_ENABLE,
            'unid' => $user?->UNID,
        ]);

        if ($user) {
            $isValid = Auth::getProvider()->validateCredentials($user, $credentials);
            \Illuminate\Support\Facades\Log::info('validateCredentials result', ['is_valid' => $isValid]);

            if (! $user->IS_IT_ENABLE) {
                \Illuminate\Support\Facades\Log::warning('Login blocked: User is disabled');
                throw ValidationException::withMessages([
                    'data.LOGON_ID' => 'هذا الحساب موقوف / معطل. يرجى مراجعة إدارة النظام.',
                ]);
            }

            if ($user->UNID != 0 && ! $user->university) {
                \Illuminate\Support\Facades\Log::warning('Login blocked: University not found for UNID ' . $user->UNID);
                throw ValidationException::withMessages([
                    'data.LOGON_ID' => 'الجامعة المرتبطة بهذا الحساب غير موجودة أو غير مسجلة بالنظام.',
                ]);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('Login failed: User not found in DB ' . config('database.connections.tenant.database'));
        }

        try {
            $response = parent::authenticate();
            \Illuminate\Support\Facades\Log::info('--- LOGIN SUCCESSFUL ---');
            return $response;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('--- LOGIN FAILED (parent::authenticate exception) ---', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getDatabaseFormComponent(),
                $this->getLogonIdFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getDatabaseFormComponent()
    {
        $options = config('academic_years.databases', []);
        $default = config('academic_years.default_database', array_key_first($options) ?: 'p_oas_db_2022');

        return Select::make('database')
            ->label('عام التنسيق')
            ->options($options)
            ->default($default)
            ->required()
            ->extraAttributes(['tabindex' => 1]);
    }

    protected function getLogonIdFormComponent(): Component
    {
        return TextInput::make('LOGON_ID')
            ->label('اسم المستخدم')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        // Store the selected database in session
        session()->put('tenant_database', $data['database']);

        // Temporarily set the database connection to the selected database for Auth::attempt
        config(['database.connections.tenant.database' => $data['database']]);
        config(['database.connections.mysql.database' => $data['database']]);
        DB::purge('tenant');
        DB::purge('mysql');
        DB::setDefaultConnection('tenant');

        return [
            'LOGON_ID' => $data['LOGON_ID'],
            'password' => $data['password'],
        ];
    }
}
