<?php

namespace App\Filament\Resources\Systems\Schemas;

use App\Models\System;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SystemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('english_name')
                    ->placeholder('-'),
                TextEntry::make('slug'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('url')
                    ->placeholder('-'),
                TextEntry::make('thumbnail')
                    ->placeholder('-'),
                TextEntry::make('icon')
                    ->placeholder('-'),
                TextEntry::make('color')
                    ->placeholder('-'),
                TextEntry::make('display_order')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('open_in_new_tab')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (System $record): bool => $record->trashed()),
            ]);
    }
}
