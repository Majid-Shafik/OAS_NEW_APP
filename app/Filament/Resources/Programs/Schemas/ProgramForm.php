<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UNID')
                    ->required()
                    ->numeric(),
                TextInput::make('FACULTY_IDENT')
                    ->required()
                    ->numeric(),
                TextInput::make('NEW_ID')
                    ->numeric(),
                TextInput::make('PROGRAM_NAME')
                    ->required(),
                TextInput::make('NEW_NAME'),
                TextInput::make('PROGRAM_CLASS_ID')
                    ->numeric()
                    ->default(2),
                TextInput::make('PROGRAM_LEVEL_ID')
                    ->numeric()
                    ->default(2),
                Toggle::make('IS_IT_ENABLE')
                    ->required(),
            ]);
    }
}
