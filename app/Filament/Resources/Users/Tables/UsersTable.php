<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\Gender;
use App\Filament\Filters\AcademicFilter;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use App\Models\User;
use App\Models\UserGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('USER_NAME')
            ->columns([
                TextColumn::make('UNID')
                    ->label('الجامعة')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : University::find($state)?->U_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('GROUP_IDENT')
                    ->label('المجموعة')
                    ->formatStateUsing(fn($state) => UserGroup::find($state)?->GROUP_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('الدور الوظيفي')
                    ->formatStateUsing(fn ($state, $record) => $record->roles->map(fn($r) => $r->label ?: $r->name)->join(', '))
                    ->badge(),
                TextColumn::make('USER_NAME')
                    ->searchable(),
                TextColumn::make('LOGON_ID')
                    ->searchable(),
                TextColumn::make('GENDER')
                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(6, $state))
                    ->searchable(),
                TextColumn::make('MOBILE_PHONE')
                    ->searchable(),
                TextColumn::make('IDENT_TYPE')
                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(7, $state))
                    ->searchable(),
                TextColumn::make('IDENT_NO')
                    ->searchable(),
                TextColumn::make('EMAIL')
                    ->searchable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('FIRST_TIME')
                    ->boolean(),
                TextColumn::make('RECORDDATE')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('inserter.USER_NAME')
                    ->label('أضيف بواسطة')
                    ->sortable(),

                TextColumn::make('FACULTY_IDENT')
                    ->label('الكلية')
                    ->words(4)
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : Faculty::find($state)?->FACULTY_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('PROGRAM_IDENT')
                    ->label('التخصص')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : Program::find($state)?->PROGRAM_NAME ?? $state)
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->label ?: $record->name)
                    ->label('الدور الوظيفي')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                SelectFilter::make('GENDER')
                    ->label('النوع (الجنس)')
                    ->options(\App\Models\ComboValue::getOptionsByCode(6)),
                SelectFilter::make('GROUP_IDENT')
                    ->label('المجموعة')
                    ->options(UserGroup::pluck('GROUP_NAME', 'GROUP_IDENT'))
                    ->searchable(),
                SelectFilter::make('IS_IT_ENABLE')
                    ->label('حالة الحساب')
                    ->options([
                        1 => 'مفعل',
                        0 => 'معطل',
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('changePassword')
                    ->visible(fn(User $record) => auth()->user()->can('ResetPassword:User', $record))
                    ->label('')
                    ->tooltip('تهيئة / تغيير كلمة المرور')
                    ->modalHeading('تهيئة / تغيير كلمة المرور للمستخدم')
                    ->modalSubmitActionLabel('حفظ كلمة المرور')
                    ->modalCancelActionLabel('إلغاء')
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'LOGON_PASS' => Hash::make($data['new_password']),
                        ]);
                        Notification::make()
                            ->title('تم تغيير كلمة المرور بنجاح.')
                            ->success()
                            ->send();
                    })
                    ->form([
                        TextInput::make('new_password')
                            ->password()
                            ->label('كلمة المرور الجديدة')
                            ->required()
                            ->rule(Password::default()),
                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->label('تأكيد كلمة المرور الجديدة')
                            ->rule('required', fn($get) => !!$get('new_password'))
                            ->same('new_password'),
                    ])
                    ->icon(Heroicon::OutlinedKey),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
