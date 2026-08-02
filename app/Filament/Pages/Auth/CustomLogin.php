<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class CustomLogin extends BaseLogin
{
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
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        return [
            'LOGON_ID' => $data['LOGON_ID'],
            'password' => $data['password'],
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        return $response;
    }
}
