<?php

namespace App\Filament\Resources\Faculties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FacultiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('university.U_NAME')
                    ->label(__('University'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('NEW_ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('FACULTY_NAME')
                    ->searchable(),
                TextColumn::make('NEW_NAME')
                    ->searchable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->boolean(),
                TextColumn::make('F_ACCEPT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('F_ACCEPT_EXAM_IDENT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('SHOW_CONFIRMED')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ORDERING_MOD_ID')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('USE_LIMIT_APPS')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ORDERBY')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('FACULTY_ORDER')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('UNID')
                    ->label(__('University'))
                    ->relationship('university', 'U_NAME'),
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
