<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Tables;

use App\Filament\Filters\AcademicFilter;
use App\Models\StudyType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppBillIdentCanceledsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('IDENT')
                    ->label('الرقم')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('APP_BILL_IDENT')
                    ->label('رقم الحافظة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('applications.APPLICANT_IDENT')
                    ->label('رقم التنسيق')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('applications.university.U_NAME')
                    ->label('الجامعة')
                    ->sortable()
                    ->searchable()
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('applications.faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->sortable()
                    ->searchable()
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('applications.program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->sortable()
                    ->searchable()
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('applications.studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->sortable()
                    ->searchable()
                    ->listWithLineBreaks()
                    ->limitList(3),
                TextColumn::make('PAYMENT')
                    ->label('المبلغ')
                    ->sortable(),
                TextColumn::make('paymentMethod.PAY_METHOD')
                    ->label('طريقة الدفع')
                    ->sortable(),
                TextColumn::make('BONDS_ID')
                    ->label('رقم السند')
                    ->sortable(),
                TextColumn::make('BONDS_DATE')
                    ->label('تاريخ السند')
                    ->date()
                    ->sortable(),
                TextColumn::make('paymentBy.USER_NAME')
                    ->label('بواسطة (الدفع)')
                    ->sortable(),
                TextColumn::make('ACTUAL_PAYMENT_DATE')
                    ->label('تاريخ الدفع الفعلي')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('canceledBy.USER_NAME')
                    ->label('بواسطة (الإلغاء)')
                    ->sortable(),
                TextColumn::make('CANCELED_ON')
                    ->label('تاريخ الإلغاء')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make('university_faculty_program', 'FACULTY_IDENT', 'PROGRAM_IDENT', 'applications'),
                SelectFilter::make('STUDYTYPE_IDENT')
                    ->label('النظام الدراسي')
                    ->options(fn () => StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                    ->query(fn (Builder $query, array $data) => $query->when(
                        filled($data['value']),
                        fn (Builder $q) => $q->whereHas('applications', fn ($aq) => $aq->where('STUDYTYPE_IDENT', $data['value']))
                    ))
                    ->searchable(),
                SelectFilter::make('PAY_METHOD_ID')
                    ->label('طريقة الدفع')
                    ->relationship('paymentMethod', 'PAY_METHOD'),
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
