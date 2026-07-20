<?php

namespace App\Filament\Resources\Faculties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FacultyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('UNID')
                    ->numeric(locale: 'en'),
                TextEntry::make('NEW_ID')
                    ->numeric(locale: 'en')
                    ->placeholder('-'),
                TextEntry::make('FACULTY_NAME'),
                TextEntry::make('NEW_NAME')
                    ->placeholder('-'),
                IconEntry::make('IS_IT_ENABLE')
                    ->boolean(),
                TextEntry::make('F_ACCEPT')
                    ->numeric(locale: 'en'),
                TextEntry::make('F_ACCEPT_EXAM_IDENT')
                    ->numeric(locale: 'en')
                    ->placeholder('-'),
                TextEntry::make('SHOW_CONFIRMED')
                    ->numeric(locale: 'en'),
                TextEntry::make('ORDERING_MOD_ID')
                    ->numeric(locale: 'en'),
                TextEntry::make('USE_LIMIT_APPS')
                    ->numeric(locale: 'en'),
                TextEntry::make('ORDERBY')
                    ->numeric(locale: 'en'),
                TextEntry::make('FACULTY_ORDER')
                    ->numeric(locale: 'en')
                    ->placeholder('-'),
            ]);
    }
}
