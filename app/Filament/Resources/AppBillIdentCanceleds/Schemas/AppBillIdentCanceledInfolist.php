<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppBillIdentCanceledInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(12)
                ->columnSpanFull()
                ->schema([
                    Section::make('بيانات الحافظة الملغاة')
                        ->schema([
                            TextEntry::make('APP_BILL_IDENT')->label('رقم الحافظة'),
                            TextEntry::make('PAYMENT')->label('المبلغ'),
                            TextEntry::make('BONDS_ID')->label('رقم السند'),
                            TextEntry::make('BONDS_DATE')->label('تاريخ السند')->date(),
                            TextEntry::make('PAYMENT_FLAG')->label('حالة الدفع'),
                            TextEntry::make('paymentMethod.PAY_METHOD')->label('طريقة الدفع'),
                            TextEntry::make('paymentBy.USER_NAME')->label('بواسطة (الدفع)'),
                            TextEntry::make('ACTUAL_PAYMENT_DATE')->label('تاريخ الدفع الفعلي')->dateTime(),
                            TextEntry::make('NOTE')->label('ملاحظات')->columnSpanFull(),
                            TextEntry::make('canceledBy.USER_NAME')->label('بواسطة (الإلغاء)'),
                            TextEntry::make('CANCELED_ON')->label('تاريخ الإلغاء')->dateTime(),
                        ])
                        ->columns(3)
                        ->columnSpan(['default' => 12, 'md' => 8]),

                    Section::make('البيانات الأكاديمية')
                        ->schema([
                            RepeatableEntry::make('applications')
                                ->label('')
                                ->schema([
                                    TextEntry::make('APPLICANT_IDENT')->label('رقم التنسيق'),
                                    TextEntry::make('university.U_NAME')->label('الجامعة'),
                                    TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                                    TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                                ])
                                ->columns(1)
                        ])
                        ->columnSpan(['default' => 12, 'md' => 4]),
                ]),
        ]);
    }
}
