<?php

namespace App\Filament\Resources\Provinces\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProvinceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('بيانات المحافظة')
                    ->schema([
                        TextInput::make('NAME')
                            ->label('الاسم بالعربية')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('ENG_NAME')
                            ->label('الاسم بالإنجليزية')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
