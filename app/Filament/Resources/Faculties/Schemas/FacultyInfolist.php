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
                    ->numeric(),
                TextEntry::make('NEW_ID')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('FACULTY_NAME'),
                TextEntry::make('NEW_NAME')
                    ->placeholder('-'),
                IconEntry::make('IS_IT_ENABLE')
                    ->boolean(),
                TextEntry::make('F_ACCEPT')
                    ->numeric(),
                TextEntry::make('F_ACCEPT_EXAM_IDENT')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('SHOW_CONFIRMED')
                    ->numeric(),
                TextEntry::make('ORDERING_MOD_ID')
                    ->numeric(),
                TextEntry::make('USE_LIMIT_APPS')
                    ->numeric(),
                TextEntry::make('ORDERBY')
                    ->numeric(),
                TextEntry::make('FACULTY_ORDER')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
