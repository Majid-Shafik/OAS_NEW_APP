<?php

namespace App\Filament\Resources\Universities\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class UniversitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')
                    ->label(__('UNID'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('U_NAME')
                    ->label(__('U_NAME'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('EN_U_NAME')
                    ->label(__('EN_U_NAME'))
                    ->sortable()
                    ->searchable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE'))
                    ->options([
                        '1' => 'مفعل',
                        '0' => 'غير مفعل',
                    ])
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
