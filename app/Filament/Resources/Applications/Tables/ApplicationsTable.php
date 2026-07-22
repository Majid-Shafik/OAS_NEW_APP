<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Filament\Filters\AcademicFilter;
use App\Models\StudyType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')
                    ->label(__('UNID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('APPLICANT_IDENT')
                    ->label(__('APPLICANT_IDENT'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('applicant.FULL_NAME')
                    ->label(__('Applicant'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label(__('Faculty'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label(__('Program'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label(__('STUDYTYPE_IDENT'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('paymentMethod.PAY_METHOD')
                    ->label('طريقة الدفع')
                    ->sortable()
                    ->searchable(),
                ColumnGroup::make('مقبول | مؤكد')
                    ->columns([
                        IconColumn::make('ACCEPTED')
                            ->label('مقبول')
                            ->boolean()
                            ->sortable(),
                        IconColumn::make('CONFIRMED_BY_APPLICANT')
                            ->label('مؤكد')
                            ->boolean()
                            ->sortable(),
                    ]),
                TextColumn::make('STATUS')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make(),
                SelectFilter::make('PAYMENT_FLAG')
                    ->label('طريقة الدفع')
                    ->relationship('paymentMethod', 'PAY_METHOD'),
                SelectFilter::make('STUDYTYPE_IDENT')
                    ->label('نوع الدراسة')
                    ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT')),
                SelectFilter::make('STATUS')
                    ->label('الحالة')
                    ->options(ApplicationStatus::class),
                TernaryFilter::make('ACCEPTED')
                    ->label('حالة القبول')
                    ->placeholder('الكل')
                    ->trueLabel('مقبول')
                    ->falseLabel('غير مقبول'),
                TernaryFilter::make('CONFIRMED_BY_APPLICANT')
                    ->label('حالة التأكيد')
                    ->placeholder('الكل')
                    ->trueLabel('مؤكد')
                    ->falseLabel('غير مؤكد'),
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
