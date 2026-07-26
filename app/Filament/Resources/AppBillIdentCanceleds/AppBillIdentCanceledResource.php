<?php

namespace App\Filament\Resources\AppBillIdentCanceleds;

use App\Filament\Resources\AppBillIdentCanceleds\Pages;
use App\Filament\Resources\AppBillIdentCanceleds\Schemas\AppBillIdentCanceledForm;
use App\Filament\Resources\AppBillIdentCanceleds\Schemas\AppBillIdentCanceledInfolist;
use App\Filament\Resources\AppBillIdentCanceleds\Tables\AppBillIdentCanceledsTable;
use App\Models\AppBillIdentCanceled;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AppBillIdentCanceledResource extends Resource
{
    protected static ?string $model = AppBillIdentCanceled::class;

    protected static ?string $modelLabel = 'حافظة ملغاة';

    protected static ?string $pluralModelLabel = 'الحوافظ الملغاة';



    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMinus;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';


    public static function form(Schema $schema): Schema
    {
        return AppBillIdentCanceledForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppBillIdentCanceledInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppBillIdentCanceledsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppBillIdentCanceleds::route('/'),
            'create' => Pages\CreateAppBillIdentCanceled::route('/create'),
            'view' => Pages\ViewAppBillIdentCanceled::route('/{record}'),
            'edit' => Pages\EditAppBillIdentCanceled::route('/{record}/edit'),
        ];
    }
}
