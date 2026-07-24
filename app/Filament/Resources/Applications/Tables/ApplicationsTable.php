<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Filament\Filters\AcademicFilter;
use App\Models\StudyType;
use Filament\Actions\Action;
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
use Illuminate\Database\Eloquent\Builder;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->columns([
                TextColumn::make('index')
                    ->rowIndex()
                    ->label('مسلسل'),
                TextColumn::make('UNID')
                    ->label(__('UNID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT'))
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('APPLICANT_IDENT')
                //     ->label(__('APPLICANT_IDENT'))
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('applicant.FULL_NAME')
                    ->label('المتقدم')
                    ->searchable()
                    ->url(fn($record) => $record->applicant ? \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $record->applicant]) : null)
                    ->tooltip('انقر هنا للانتقال إلى ملف المتقدم')
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->extraAttributes(['class' => 'underline font-bold']),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_RATE')
                    ->label('معدل الثانوية')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->searchable(),
                TextColumn::make('PAYMENT_FLAG')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable(),
                TextColumn::make('APP_BILL_IDENT')
                    ->label('رقم السند')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('RECORDDATE')
                    ->label('تاريخ التسديد / الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('insertedBy.USER_NAME')
                    ->label('بواسطة')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('CONFIRMED_ON')
                    ->label('تاريخ التأكيد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('CHOICE_NO')
                    ->label('رقم الرغبة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('FINAL_MARK')
                    ->label('المفاضلة')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('STUDENT_CODE')
                    ->label('رقم الطالب')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('EXPORTED')
                    ->label('مُصدَّر (EXPORTED)')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColumnGroup::make('مقبول | مؤكد')
                    ->columns([
                        IconColumn::make('ACCEPTED')
                            ->label('مقبول')
                            ->boolean()
                            ->trueColor('info')
                            ->falseColor('gray')
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
                    ->label('النظام الدراسي')
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
                TernaryFilter::make('is_paid')
                    ->label('حالة التسديد')
                    ->placeholder('الكل')
                    ->trueLabel('مسدد')
                    ->falseLabel('غير مسدد')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('PAYMENT_FLAG')->where('PAYMENT_FLAG', '!=', 0),
                        false: fn(Builder $query) => $query->where(fn(Builder $q) => $q->whereNull('PAYMENT_FLAG')->orWhere('PAYMENT_FLAG', 0)),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('قبول')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn($record) => !empty($record->PAYMENT_FLAG) && $record->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE && ! $record->ACCEPTED)
                    ->hidden(fn($record) => ! auth()->user()->can('accept', $record))
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update([
                        'ACCEPTED' => 1,
                        'STATUS' => ApplicationStatus::Accept,
                    ])),

                Action::make('pay')
                    ->label('تسديد')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->visible(fn($record) => (empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE))
                    ->hidden(fn($record) => ! auth()->user()->can('pay', $record))
                    ->form([
                        \Filament\Forms\Components\Select::make('PAYMENT_FLAG')
                            ->label('طريقة الدفع')
                            ->relationship('paymentMethod', 'PAY_METHOD', fn($query) => $query->where('IS_ENABLED', 1))
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('APP_BILL_IDENT')
                            ->label('رقم السند')
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('RECORDDATE')
                            ->label('تاريخ التسديد')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(fn($record, array $data) => $record->update([
                        'PAYMENT_FLAG' => $data['PAYMENT_FLAG'],
                        'APP_BILL_IDENT' => $data['APP_BILL_IDENT'],
                        'RECORDDATE' => $data['RECORDDATE'],
                        'INSERTED_BY' => auth()->id(),
                    ])),

                Action::make('confirm')
                    ->label('تأكيد')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn($record) => !empty($record->PAYMENT_FLAG) && $record->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE && ! $record->CONFIRMED_BY_APPLICANT)
                    ->hidden(fn($record) => ! auth()->user()->can('confirm', $record))
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\TextInput::make('STUDENT_CODE')
                            ->label('رقم القيد الجامعي')
                            ->required()
                            ->rules([
                                fn($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $exists = \App\Models\Application::where('UNID', $record->UNID)
                                        ->where('STUDENT_CODE', $value)
                                        ->where('APPLICATION_IDENT', '!=', $record->APPLICATION_IDENT)
                                        ->exists();
                                    if ($exists) {
                                        $fail("رقم القيد ({$value}) مستخدم بالفعل لطالب آخر في نفس الجامعة.");
                                    }
                                }
                            ])
                    ])
                    ->action(fn($record, array $data) => $record->update([
                        'CONFIRMED_BY_APPLICANT' => 1,
                        'STUDENT_CODE' => $data['STUDENT_CODE'],
                    ])),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('SEC_SCHOOL_RATE', 'desc');
    }
}
