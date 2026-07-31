<?php

namespace App\Filament\Resources\Provinces\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProvincesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ID')
                    ->label('الرقم')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('NAME')
                    ->label('الاسم بالعربية')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ENG_NAME')
                    ->label('الاسم بالإنجليزية')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
