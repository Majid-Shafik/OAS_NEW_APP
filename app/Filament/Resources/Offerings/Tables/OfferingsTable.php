<?php

namespace App\Filament\Resources\Offerings\Tables;

use App\Filament\Filters\AcademicFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfferingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('OFFERING_IDENT')
                    ->label('الرقم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_TYPE')
                    ->label('نوع الثانوية')
                    ->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(1, $state))
                    ->searchable(),
                TextColumn::make('offeringGroup.DESCRIPTION')
                    ->label('مجموعة التنسيق')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_ACCEPT_RATE')
                    ->label('معدل القبول')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('ENTRANCE_EXAM_WEIGHT')
                    ->label('وزن امتحان القبول')
                    ->numeric()
                    ->sortable(),

                ColumnGroup::make('فترة التنسيق', [
                    TextColumn::make('FROM_DATE')
                        ->label('من تاريخ')
                        ->date()
                        ->sortable(),
                    TextColumn::make('TO_DATE')
                        ->label('إلى تاريخ')
                        ->date()
                        ->sortable(),
                ]),
                ColumnGroup::make('عمر الثانوية', [
                    TextColumn::make('Y_SEC_SCHOOL_MAX_AGE')
                        ->label('عمر الثانوي (يمني)')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('NY_SEC_SCHOOL_MAX_AGE')
                        ->label('عمر الثانوي (غير يمني)')
                        ->numeric()
                        ->sortable(),
                ]),
                IconColumn::make('ENTRANCE_EXAM_REQUIRED')
                    ->label('امتحان مطلوب')
                    ->boolean(),
                ColumnGroup::make('معلومات التسجيل والمراجعة', [
                    TextColumn::make('lastUpdatedBy.USER_NAME')
                        ->label('تم التعديل بواسطة')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('LAST_UPDATED_ON')
                        ->label('تاريخ التحديث')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('approvalBy.USER_NAME')
                        ->label('الاعتماد بواسطة')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('APPROVAL_ON')
                        ->label('تاريخ الاعتماد')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    IconColumn::make('APPROVAL')
                        ->label('حالة الاعتماد')
                        ->boolean()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                AcademicFilter::make(),
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
