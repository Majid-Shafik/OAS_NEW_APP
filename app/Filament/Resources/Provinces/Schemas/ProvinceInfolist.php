<?php

namespace App\Filament\Resources\Provinces\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Schema;

class ProvinceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('بيانات المحافظة')
                    ->schema([
                        TextEntry::make('NAME')
                            ->label('الاسم بالعربية'),
                        TextEntry::make('ENG_NAME')
                            ->label('الاسم بالإنجليزية'),
                    ])
                    ->columns(2),
            ]);
    }
}
