<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                TextColumn::make('applications.program.PROGRAM_NAME')
                    ->label('التخصص')
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
                \App\Filament\Filters\AcademicFilter::make('university_faculty_program', 'FACULTY_IDENT', 'PROGRAM_IDENT', 'applications'),
                \Filament\Tables\Filters\SelectFilter::make('PAY_METHOD_ID')
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
