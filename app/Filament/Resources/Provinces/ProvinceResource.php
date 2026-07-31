<?php

namespace App\Filament\Resources\Provinces;

use App\Filament\Resources\Provinces\Pages\CreateProvince;
use App\Filament\Resources\Provinces\Pages\EditProvince;
use App\Filament\Resources\Provinces\Pages\ListProvinces;
use App\Filament\Resources\Provinces\Pages\ViewProvince;
use App\Filament\Resources\Provinces\Schemas\ProvinceForm;
use App\Filament\Resources\Provinces\Schemas\ProvinceInfolist;
use App\Filament\Resources\Provinces\Tables\ProvincesTable;
use App\Models\Province;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProvinceResource extends Resource
{
    protected static ?string $model = Province::class;

    protected static ?string $modelLabel = 'محافظة';

    protected static ?string $pluralModelLabel = 'المحافظات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static UnitEnum|string|null $navigationGroup = 'الإعدادات الأساسية';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'NAME';

    public static function form(Schema $schema): Schema
    {
        return ProvinceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProvinceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProvincesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProvinces::route('/'),
            'create' => CreateProvince::route('/create'),
            'view' => ViewProvince::route('/{record}'),
            'edit' => EditProvince::route('/{record}/edit'),
        ];
    }
}
