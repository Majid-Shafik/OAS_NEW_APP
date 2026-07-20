<?php

namespace App\Filament\Resources\Applicants\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university.U_NAME')
                    ->label(__('UNID'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('APPLICANT_IDENT')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('NATIONAL_NUMBER')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('FIRST_NAME')
                    ->searchable(),
                TextColumn::make('LAST_NAME')
                    ->searchable(),
                TextColumn::make('FULL_NAME')
                    ->searchable(),
                TextColumn::make('applications_count')
                    ->state(fn ($record) => $record->applications()->count())
                    ->label('عدد التقديمات')
                    ->badge(),
                TextColumn::make('PLACE_OF_BIRTH')
                    ->searchable(),
                TextColumn::make('DATE_OF_BIRTH')
                    ->date()
                    ->sortable(),
                TextColumn::make('PROVINCE')
                    ->searchable(),
                TextColumn::make('TERRITORY')
                    ->searchable(),

                TextColumn::make('COUNTRY_NAME')
                    ->searchable(),
                TextColumn::make('IDENT_TYPE')
                    ->searchable(),
                TextColumn::make('IDENT_NO')
                    ->searchable(),
                IconColumn::make('YEMEN_NATIONAL')
                    ->boolean(),
                TextColumn::make('SEC_SCHOOL_TYPE')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_PLACE')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_PROVINCE')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_TERRITORY')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_YEAR')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_NAME')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_RATE')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_SEATNO')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_MARK')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_OVERALLMARK')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('ADMITTED_OFFERING')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label(__('ADMITTED_PROGRAM'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label(__('ADMITTED_FACULITY'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ADMITTED_ON')
                    ->date()
                    ->sortable(),
                TextColumn::make('EMAIL')
                    ->searchable(),
                TextColumn::make('MOBILE_PHONE')
                    ->searchable(),
                TextColumn::make('BLOOD_GROUP')
                    ->searchable(),
                TextColumn::make('GENDER')
                    ->label(__('GENDER'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('RECORDDATE')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('STATUS')
                    ->badge()
                    ->searchable(),
                IconColumn::make('FREEZE')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('INSERTED_BY')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('LAST_UPDATED_BY')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('LAST_UPDATED_ON')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('APPROVED_BY')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('APPROVED_ON')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('IMPORTED')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('APPLICANT_TYPE')
                    ->formatStateUsing(function ($state, $record) {
                        $tt = 'A';
                        if ($state == 2) {
                            $tt = 'B';
                        }
                        if ($record->IS_CLEARING == 0) {
                            return $tt . '-' . 'اعتيادي';
                        } else {
                            return $tt . '-' . 'مقاصة';
                        }
                    })
                    ->sortable(),
                IconColumn::make('IS_CLEARING')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('REVIEWED')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('REVIEW_BY')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('REVIEW_ON')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('REJECT_REASON')
                    ->searchable(),
                IconColumn::make('SECOND_REVIEWED')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('SECOND_REVIEWED_BY')
                    ->numeric(locale: 'en')
                    ->sortable(),
                TextColumn::make('SECOND_REVIEWED_ON')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('SECOND_REJECT_REASON')
                    ->searchable(),
                IconColumn::make('EXPORTED')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                \App\Filament\Filters\AcademicFilter::make('university_faculty_program', 'ADMITTED_FACULITY', 'ADMITTED_PROGRAM'),
                \Filament\Tables\Filters\SelectFilter::make('GENDER')
                    ->label('الجنس')
                    ->options(\App\Enums\Gender::class),
                \Filament\Tables\Filters\SelectFilter::make('STATUS')
                    ->label('حالة الملف')
                    ->options(\App\Enums\ApplicantStatus::class),
                \Filament\Tables\Filters\TernaryFilter::make('FREEZE')
                    ->label('حالة التجميد')
                    ->placeholder('الكل')
                    ->trueLabel('مجمد')
                    ->falseLabel('غير مجمد'),
                \Filament\Tables\Filters\SelectFilter::make('SEC_SCHOOL_TYPE')
                    ->label('نوع الثانوية')
                    ->options(fn () => \App\Models\Applicant::distinct()->whereNotNull('SEC_SCHOOL_TYPE')->pluck('SEC_SCHOOL_TYPE', 'SEC_SCHOOL_TYPE')->filter(fn($v) => !empty($v))->toArray())
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('COUNTRY_NAME')
                    ->label('الدولة')
                    ->options(fn () => \App\Models\Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => !empty($v))->toArray())
                    ->searchable(),
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
