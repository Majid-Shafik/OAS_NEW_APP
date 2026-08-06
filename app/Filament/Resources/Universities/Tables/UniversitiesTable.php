<?php

namespace App\Filament\Resources\Universities\Tables;

use App\Models\University;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UniversitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')
                    ->label(__('UNID'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('U_NAME')
                    ->label(__('U_NAME'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('EN_U_NAME')
                    ->label(__('EN_U_NAME'))
                    ->sortable()
                    ->searchable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('IS_IT_ENABLE')
                    ->label(__('IS_IT_ENABLE'))
                    ->options([
                        '1' => 'مفعل',
                        '0' => 'غير مفعل',
                    ]),
                SelectFilter::make('ENABLE_CONFIRMED')
                    ->label('حالة تفعيل القبول والتأكيد')
                    ->options([
                        '1' => 'مفعل',
                        '0' => 'غير مفعل',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('paymentSettings')
                    ->label('إعدادات')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->authorize('updateUniversitySettings')
                    ->visible(fn(University $record) => auth()->user()->can('updateUniversitySettings', $record))
                    ->schema([
                        Fieldset::make('إعدادات السداد')
                        ->schema([
                        Toggle::make('PAY_METHOD_POST')
                            ->label('تفعيل السداد عبر البريد'),
                        Toggle::make('PAY_METHOD_CAC')
                            ->label('تفعيل السداد عبر كاك بنك'),
                        Toggle::make('PAY_METHOD_UN')
                            ->label('تفعيل السداد عبر الجامعة'),
                        ]),
                        
                        Fieldset::make('إعدادات القبول والتأكيد')
                        ->schema([
                            Toggle::make('ENABLE_CONFIRMED')
                                ->label('السماح بقبول وتأكيد الطلاب في الجامعة')
                                ->helperText('عند التفعيل، يتم السماح بعمليات قبول وتأكيد الطلاب للجامعة'),
                        ]),

                        Fieldset::make('إعدادات المقاصة')
                        ->schema([
                            Toggle::make('CLEARING_UN')
                                ->label('جامعة مقاصة')
                                ->helperText('تحديد ما إذا كانت هذه الجامعة خاصة بنظام المقاصة'),
                        ]),
                        TextInput::make('GS_TITLE_PAYMENT')
                            ->label('عنوان حافظة السداد')
                            ->maxLength(255)
                            ->hint('مثل: حافظة توريد رسوم التسجيل لحساب جامعة كذا وكذا'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'PAY_METHOD_POST' => $data['PAY_METHOD_POST'] ? 1 : 0,
                            'PAY_METHOD_CAC' => $data['PAY_METHOD_CAC'] ? 1 : 0,
                            'PAY_METHOD_UN' => $data['PAY_METHOD_UN'] ? 1 : 0,
                            'ENABLE_CONFIRMED' => $data['ENABLE_CONFIRMED'] ? 1 : 0,
                            'CLEARING_UN' => $data['CLEARING_UN'] ? 1 : 0,
                            'GS_TITLE_PAYMENT' => $data['GS_TITLE_PAYMENT'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('تم حفظ الإعدادات بنجاح')
                            ->success()
                            ->send();
                    })
                    ->fillForm(fn ($record) => [
                        'PAY_METHOD_POST' => (bool) $record->PAY_METHOD_POST,
                        'PAY_METHOD_CAC' => (bool) $record->PAY_METHOD_CAC,
                        'PAY_METHOD_UN' => (bool) $record->PAY_METHOD_UN,
                        'ENABLE_CONFIRMED' => (bool) $record->ENABLE_CONFIRMED,
                        'CLEARING_UN' => (bool) $record->CLEARING_UN,
                        'GS_TITLE_PAYMENT' => $record->GS_TITLE_PAYMENT,
                    ]),
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
