<?php

namespace App\Filament\Resources\ProgramCapacities\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramCapacitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ENROLLMENT_CAPACITY')
                    ->label('الطاقة الاستيعابية')
                    ->sortable(),
                TextColumn::make('ACADEMIC_YEAR')
                    ->label('العام الجامعي')
                    ->sortable(),
                TextColumn::make('histories_count')
                    ->counts('histories')
                    ->label('عدد التعديلات')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
