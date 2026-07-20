<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('UNID')
                    ->numeric(),
                TextEntry::make('FACULTY_IDENT')
                    ->numeric(),
                TextEntry::make('NEW_ID')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('PROGRAM_NAME'),
                TextEntry::make('NEW_NAME')
                    ->placeholder('-'),
                TextEntry::make('PROGRAM_CLASS_ID')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('PROGRAM_LEVEL_ID')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('IS_IT_ENABLE')
                    ->boolean(),
            ]);
    }
}
