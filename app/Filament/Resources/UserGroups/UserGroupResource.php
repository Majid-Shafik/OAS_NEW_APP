<?php

namespace App\Filament\Resources\UserGroups;

use App\Filament\Resources\UserGroups\Pages\ManageUserGroups;
use App\Models\UserGroup;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserGroupResource extends Resource
{
    protected static ?string $model = UserGroup::class;

    protected static ?string $modelLabel = 'مجموعة المستخدمين';

    protected static ?string $pluralModelLabel = 'مجموعات المستخدمين';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static UnitEnum|string|null $navigationGroup = 'المستخدمين والصلاحيات';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'GROUP_NAME';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('GROUP_NAME')
                    ->label('اسم المجموعة')
                    ->required(),
                TextInput::make('NOTE')
                    ->label('ملاحظات'),
                Toggle::make('IS_IT_ENABLE')
                    ->label('تفعيل')
                    ->default(true)
                    ->required(),
                Toggle::make('EDITABLE')
                    ->label('قابل للتعديل')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('GROUP_NAME')
            ->columns([
                TextColumn::make('GROUP_NAME')
                    ->label('اسم المجموعة')
                    ->searchable(),
                TextColumn::make('NOTE')
                    ->label('ملاحظات')
                    ->searchable(),
                IconColumn::make('IS_IT_ENABLE')
                    ->label('مفعل')
                    ->boolean(),
                IconColumn::make('EDITABLE')
                    ->label('قابل للتعديل')
                    ->boolean(),
            ])
            ->filters([
                //
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
            'index' => ManageUserGroups::route('/'),
        ];
    }
}
