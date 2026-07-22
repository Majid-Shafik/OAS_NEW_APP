<?php

namespace App\Filament\Resources\Universities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UniversityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UNID')
                    ->label(__('UNID'))
                    ->required()
                    ->numeric(),
                TextInput::make('U_NAME')
                    ->label(__('U_NAME'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('EN_U_NAME')
                    ->label(__('EN_U_NAME'))
                    ->maxLength(255),
                Toggle::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE')),
            ]);
    }
}
