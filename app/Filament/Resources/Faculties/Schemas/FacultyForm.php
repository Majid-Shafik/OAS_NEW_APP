<?php

namespace App\Filament\Resources\Faculties\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FacultyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UNID')
                    ->required()
                    ->numeric(locale: 'en'),
                TextInput::make('NEW_ID')
                    ->numeric(locale: 'en'),
                TextInput::make('FACULTY_NAME')
                    ->required(),
                TextInput::make('NEW_NAME'),
                Toggle::make('IS_IT_ENABLE')
                    ->required(),
                TextInput::make('F_ACCEPT')
                    ->required()
                    ->numeric(locale: 'en')
                    ->default(0),
                TextInput::make('F_ACCEPT_EXAM_IDENT')
                    ->numeric(locale: 'en'),
                TextInput::make('SHOW_CONFIRMED')
                    ->required()
                    ->numeric(locale: 'en')
                    ->default(0),
                TextInput::make('ORDERING_MOD_ID')
                    ->required()
                    ->numeric(locale: 'en')
                    ->default(1),
                TextInput::make('USE_LIMIT_APPS')
                    ->required()
                    ->numeric(locale: 'en')
                    ->default(0),
                TextInput::make('ORDERBY')
                    ->required()
                    ->numeric(locale: 'en')
                    ->default(1),
                TextInput::make('FACULTY_ORDER')
                    ->numeric(locale: 'en')
                    ->default(1),
            ]);
    }
}
