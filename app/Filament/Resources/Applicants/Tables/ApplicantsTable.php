<?php

namespace App\Filament\Resources\Applicants\Tables;

use App\Enums\ApplicantStatus;
use App\Enums\Gender;
use App\Filament\Filters\AcademicFilter;
use App\Models\Applicant;
use App\Models\Country;
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

class ApplicantsTable
{
    public static function configure(Table $table): Table
    {
        $isClearing = str_contains(get_class($table->getLivewire()), 'Clearing');

        return $table
            ->columns([
                TextColumn::make('index')
                    ->rowIndex()
                    ->label('مسلسل'),
                TextColumn::make('university.U_NAME')
                    ->label(__('UNID'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('APPLICANT_IDENT')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('NATIONAL_NUMBER')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('FIRST_NAME')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('LAST_NAME')
                    ->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('FULL_NAME')
                    ->searchable(),
                TextColumn::make('applications_count')
                    ->counts('applications')
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
                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(7, $state))
                    ->searchable(),
                TextColumn::make('IDENT_NO')
                    ->searchable(),
                IconColumn::make('YEMEN_NATIONAL')
                    ->boolean(),
                ColumnGroup::make('بيانات الثانوية')
                    ->columns([
                        TextColumn::make('SEC_SCHOOL_TYPE')
                            ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(1, $state))
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_PLACE')
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_PROVINCE')
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_TERRITORY')
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_NAME')
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_RATE')
                            ->numeric()
                            ->sortable(),
                        TextColumn::make('SEC_SCHOOL_SEATNO')
                            ->searchable(),
                        TextColumn::make('SEC_SCHOOL_YEAR')
                            ->numeric()
                            ->sortable(),
                        TextColumn::make('SEC_SCHOOL_MARK')
                            ->numeric()
                            ->sortable(),
                        TextColumn::make('SEC_SCHOOL_OVERALLMARK')
                            ->numeric()
                            ->sortable(),
                    ]),
                TextColumn::make('ADMITTED_OFFERING')
                    ->numeric()
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
                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(8, $state))
                    ->searchable(),
                TextColumn::make('GENDER')
                    ->label(__('GENDER'))
                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(6, $state))
                    ->badge()
                    ->searchable(),
                TextColumn::make('RECORDDATE')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('STATUS')
                    ->badge()
                    ->searchable(),
                TextColumn::make('FREEZE')
                    ->label('التجميد')
                    ->badge()
                    ->sortable(),
                TextColumn::make('INSERTED_BY')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('LAST_UPDATED_BY')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('LAST_UPDATED_ON')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('APPROVED_BY')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('APPROVED_ON')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('IMPORTED')
                    ->label('طريقة الإدخال')
                    ->translateFromConfig('imported')
                    ->sortable(),
                TextColumn::make('APPLICANT_TYPE')
                    ->label('نوع المتقدم')
                    ->translateFromConfig('applicant_type')
                    ->sortable(),
                TextColumn::make('IS_CLEARING')
                    ->label('المقاصة')
                    ->badge()
                    ->visible($isClearing),
                IconColumn::make('REVIEWED')
                    ->label('المراجعة الأولى')
                    ->icon(fn ($state): string => match ((string) $state) {
                        '1' => 'heroicon-o-check-circle',
                        '2' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn ($state): string => match ((string) $state) {
                        '1' => 'success',
                        '2' => 'danger',
                        default => 'warning',
                    })
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('REVIEW_BY')
                    ->numeric()
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('REVIEW_ON')
                    ->dateTime()
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('REJECT_REASON')
                    ->visible($isClearing)
                    ->searchable(),
                IconColumn::make('SECOND_REVIEWED')
                    ->label('المراجعة الثانية')
                    ->icon(fn ($state): string => match ((string) $state) {
                        '1' => 'heroicon-o-check-circle',
                        '2' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn ($state): string => match ((string) $state) {
                        '1' => 'success',
                        '2' => 'danger',
                        default => 'warning',
                    })
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('SECOND_REVIEWED_BY')
                    ->numeric()
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('SECOND_REVIEWED_ON')
                    ->dateTime()
                    ->visible($isClearing)
                    ->sortable(),
                TextColumn::make('SECOND_REJECT_REASON')
                    ->visible($isClearing)
                    ->searchable(),
                IconColumn::make('EXPORTED')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make('university_faculty_program', 'ADMITTED_FACULITY', 'ADMITTED_PROGRAM'),
                SelectFilter::make('GENDER')
                    ->label('الجنس')
                    ->options(\App\Models\ComboValue::where('CODE', 6)->pluck('VALUE', 'VALUE')),
                SelectFilter::make('STATUS')
                    ->label('حالة الملف')
                    ->options(ApplicantStatus::class),
                SelectFilter::make('FREEZE')
                    ->label('حالة التجميد')
                    ->options(\App\Enums\FreezeStatus::class),
                SelectFilter::make('SEC_SCHOOL_TYPE')
                    ->label('نوع الثانوية')
                    ->options(\App\Models\ComboValue::where('CODE', 1)->pluck('VALUE', 'VALUE'))
                    ->searchable(),
                SelectFilter::make('IS_CLEARING')
                    ->label('نوع الطالب (مقاصة / اعتيادي)')
                    ->options(\App\Enums\IsClearingType::class)
                    ->hidden(),
                SelectFilter::make('APPLICANT_TYPE')
                    ->label('نوع المتقدم')
                    ->options(config('p.default.applicant_type', [])),
                SelectFilter::make('PROVINCE')
                    ->label('المحافظة')
                    ->options(fn() => Applicant::select('PROVINCE')->distinct()->whereNotNull('PROVINCE')->pluck('PROVINCE', 'PROVINCE')->toArray())
                    ->searchable(),
                SelectFilter::make('SEC_SCHOOL_PROVINCE')
                    ->label('محافظة الثانوية')
                    ->options(fn() => Applicant::select('SEC_SCHOOL_PROVINCE')->distinct()->whereNotNull('SEC_SCHOOL_PROVINCE')->pluck('SEC_SCHOOL_PROVINCE', 'SEC_SCHOOL_PROVINCE')->toArray())
                    ->searchable(),
                SelectFilter::make('COUNTRY_NAME')
                    ->label('الدولة')
                    ->options(fn() => Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => ! empty($v))->toArray())
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('FirstReviewResult')
                    ->label('نتيجة المراجعة الأولى')
                    ->visible($isClearing)
                    ->options([
                        '0' => 'لم تتم المراجعة',
                        '1' => 'مقبول (معتمد)',
                        '2' => 'مرفوض',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if ($data['value'] === '0') {
                            $query->where(fn ($q) => $q->whereNull('REVIEWED')->orWhere('REVIEWED', 0));
                        } elseif ($data['value'] === '1') {
                            $query->where('REVIEWED', 1);
                        } elseif ($data['value'] === '2') {
                            $query->where('REVIEWED', 2);
                        }
                    }),
                \Filament\Tables\Filters\SelectFilter::make('SecondReviewResult')
                    ->label('نتيجة المراجعة الثانية')
                    ->visible($isClearing)
                    ->options([
                        '0' => 'لم تتم المراجعة',
                        '1' => 'مقبول (معتمد)',
                        '2' => 'مرفوض',
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if ($data['value'] === '0') {
                            $query->where(fn ($q) => $q->whereNull('SECOND_REVIEWED')->orWhere('SECOND_REVIEWED', 0));
                        } elseif ($data['value'] === '1') {
                            $query->where('SECOND_REVIEWED', 1);
                        } elseif ($data['value'] === '2') {
                            $query->where('SECOND_REVIEWED', 2);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            // ->recordUrl(fn (\Illuminate\Database\Eloquent\Model $record): string => \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
