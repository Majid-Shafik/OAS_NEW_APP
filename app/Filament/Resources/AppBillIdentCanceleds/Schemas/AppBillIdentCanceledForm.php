<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AppBillIdentCanceledForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('APP_BILL_IDENT')
                ->label('رقم الحافظة')
                ->numeric()
                ->required(),
            TextInput::make('PAYMENT')
                ->label('المبلغ')
                ->numeric()
                ->required(),
            TextInput::make('BONDS_ID')
                ->label('رقم السند')
                ->numeric()
                ->required(),
            DatePicker::make('BONDS_DATE')
                ->label('تاريخ السند')
                ->required(),
            TextInput::make('PAYMENT_FLAG')
                ->label('حالة الدفع')
                ->numeric()
                ->required(),
            Select::make('PAY_METHOD_ID')
                ->label('طريقة الدفع')
                ->relationship('paymentMethod', 'PAY_METHOD')
                ->required(),
            Select::make('PAYMENT_BY')
                ->label('بواسطة (الدفع)')
                ->relationship('paymentBy', 'USER_NAME')
                ->required(),
            DateTimePicker::make('ACTUAL_PAYMENT_DATE')
                ->label('تاريخ الدفع الفعلي')
                ->required(),
            Textarea::make('NOTE')
                ->label('ملاحظات')
                ->maxLength(300)
                ->columnSpanFull()
                ->required(),
            Select::make('CANCELED_BY')
                ->label('بواسطة (الإلغاء)')
                ->relationship('canceledBy', 'USER_NAME')
                ->required(),
            DateTimePicker::make('CANCELED_ON')
                ->label('تاريخ الإلغاء')
                ->required(),
        ])->columns(3);
    }
}
