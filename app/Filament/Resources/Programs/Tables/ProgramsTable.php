<?php

namespace App\Filament\Resources\Programs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')
                    ->numeric(locale: 'en')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('university.U_NAME')
                    ->label(__('University'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('FACULTY_IDENT')
                    ->numeric(locale: 'en')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label(__('Faculty'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('NEW_ID')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('PROGRAM_NAME')
                    ->searchable(),
                TextColumn::make('NEW_NAME')
                    ->searchable(),
                TextColumn::make('PROGRAM_CLASS_ID')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('PROGRAM_LEVEL_ID')
                    ->numeric(locale: 'en')
                    ->sortable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->boolean(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('UNID')
                    ->label(__('University'))
                    ->relationship('university', 'U_NAME'),
                \Filament\Tables\Filters\SelectFilter::make('FACULTY_IDENT')
                    ->label(__('Faculty'))
                    ->options(\App\Models\Faculty::pluck('FACULTY_NAME', 'FACULTY_IDENT')),
                \Filament\Tables\Filters\SelectFilter::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE'))
                    ->options([
                        '1' => 'مفعل',
                        '0' => 'غير مفعل',
                    ]),
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
