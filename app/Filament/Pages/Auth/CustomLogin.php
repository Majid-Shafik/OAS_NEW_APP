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
        return Select::make('database')
            ->label('عام التنسيق')
            ->options([
                'p_oas_db_2022' => '2022-2021',
                'p_oas_db_2021' => '2021-2020',
                'p_oas_db_2020' => '2020-2019',
                'p_oas_db_2019' => '2019-2018',
                'p_oas_db_2018' => '2018-2017',
            ])
            ->default('p_oas_db_2022')
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
