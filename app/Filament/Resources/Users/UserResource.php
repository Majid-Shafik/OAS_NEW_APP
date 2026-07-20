<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static UnitEnum|string|null $navigationGroup = 'المستخدمين والصلاحيات';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمين';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('UNID')
                    ->label('الجامعة')
                    ->options(\App\Models\University::pluck('U_NAME', 'UNID')->prepend('غير محدد', 0))
                    ->live()
                    ->searchable()
                    ->default(0),
                \Filament\Forms\Components\Select::make('GROUP_IDENT')
                    ->label('المجموعة (الدور)')
                    ->options(\App\Models\UserGroup::pluck('GROUP_NAME', 'GROUP_IDENT'))
                    ->searchable(),
                TextInput::make('USER_NAME')
                    ->required(),
                TextInput::make('LOGON_ID')
                    ->required(),
                TextInput::make('LOGON_PASS')
                    ->required(),
                TextInput::make('GENDER')
                    ->required(),
                TextInput::make('MOBILE_PHONE')
                    ->required(),
                TextInput::make('IDENT_TYPE')
                    ->required(),
                TextInput::make('IDENT_NO')
                    ->required(),
                TextInput::make('EMAIL'),
                Toggle::make('FIRST_TIME')
                    ->required(),
                DateTimePicker::make('RECORDDATE'),
                TextInput::make('INSERTED_BY')
                    ->numeric(),
                TextInput::make('IS_IT_ENABLE')
                    ->required()
                    ->numeric()
                    ->default(1),
                \Filament\Forms\Components\Select::make('FACULTY_IDENT')
                    ->label('الكلية')
                    ->options(fn(Get $get) => \App\Models\Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT')->prepend('غير محدد', 0))
                    ->live()
                    ->searchable()
                    ->default(0),
                \Filament\Forms\Components\Select::make('PROGRAM_IDENT')
                    ->label('التخصص')
                    ->options(fn(Get $get) => \App\Models\Program::where('UNID', $get('UNID'))
                        ->where('FACULTY_IDENT', $get('FACULTY_IDENT'))
                        ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT')->prepend('غير محدد', 0))
                    ->searchable()
                    ->required()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('UNID')
                    ->label('الجامعة')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : \App\Models\University::find($state)?->U_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('GROUP_IDENT')
                    ->label('المجموعة')
                    ->formatStateUsing(fn($state) => \App\Models\UserGroup::find($state)?->GROUP_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('USER_NAME')
                    ->searchable(),
                TextColumn::make('LOGON_ID')
                    ->searchable(),
                TextColumn::make('GENDER')
                    ->searchable(),
                TextColumn::make('MOBILE_PHONE')
                    ->searchable(),
                TextColumn::make('IDENT_TYPE')
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
                TextColumn::make('INSERTED_BY')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('FACULTY_IDENT')
                    ->label('الكلية')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : \App\Models\Faculty::find($state)?->FACULTY_NAME ?? $state)
                    ->sortable(),
                TextColumn::make('PROGRAM_IDENT')
                    ->label('التخصص')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : \App\Models\Program::find($state)?->PROGRAM_NAME ?? $state)
                    ->sortable(),
            ])
            ->filters([
                \App\Filament\Filters\AcademicFilter::make(),
                \Filament\Tables\Filters\SelectFilter::make('GENDER')
                    ->label('النوع (الجنس)')
                    ->options(\App\Enums\Gender::class),
                \Filament\Tables\Filters\SelectFilter::make('GROUP_IDENT')
                    ->label('المجموعة (الدور)')
                    ->options(\App\Models\UserGroup::pluck('GROUP_NAME', 'GROUP_IDENT'))
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('IS_IT_ENABLE')
                    ->label('حالة الحساب')
                    ->options([
                        1 => 'مفعل',
                        0 => 'معطل',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
