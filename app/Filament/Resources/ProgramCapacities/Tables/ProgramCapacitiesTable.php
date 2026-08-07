<?php

namespace App\Filament\Resources\ProgramCapacities\Tables;

use App\Filament\Filters\AcademicFilter;
use App\Models\ProgramCapacity;
use App\Models\StudyType;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                TextColumn::make('program.faculty.FACULTY_NAME')
                    ->label('الكلية')
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
                TextColumn::make('histories_count')
                    ->counts('histories')
                    ->label('عدد التعديلات')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make(),
                SelectFilter::make('STUDYTYPE_IDENT')
                    ->label('النظام الدراسي')
                    ->options(fn () => StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                    ->searchable(),
                TernaryFilter::make('has_histories')
                    ->label('حالة التعديل')
                    ->placeholder('الكل')
                    ->trueLabel('تم تعديلها فقط')
                    ->falseLabel('بدون أي تعديل')
                    ->queries(
                        true: fn (Builder $query) => $query->has('histories'),
                        false: fn (Builder $query) => $query->doesntHave('histories'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
