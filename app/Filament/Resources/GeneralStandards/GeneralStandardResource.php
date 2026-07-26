<?php

namespace App\Filament\Resources\GeneralStandards;

use App\Filament\Resources\GeneralStandards\Pages\CreateGeneralStandard;
use App\Filament\Resources\GeneralStandards\Pages\EditGeneralStandard;
use App\Filament\Resources\GeneralStandards\Pages\ListGeneralStandards;
use App\Filament\Resources\GeneralStandards\Pages\ViewGeneralStandard;
use App\Filament\Resources\GeneralStandards\Schemas\GeneralStandardForm;
use App\Filament\Resources\GeneralStandards\Schemas\GeneralStandardInfolist;
use App\Filament\Resources\GeneralStandards\Tables\GeneralStandardsTable;
use App\Models\GeneralStandard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GeneralStandardResource extends Resource
{
    protected static ?string $model = GeneralStandard::class;

    protected static ?string $modelLabel = 'معيار مقاصاة';
        protected static ?int $navigationSort = 5;

    protected static ?string $pluralModelLabel = 'معايير المقاصاة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static UnitEnum|string|null $navigationGroup = 'المقاصاة';

    public static function form(Schema $schema): Schema
    {
        return GeneralStandardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeneralStandardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneralStandardsTable::configure($table);
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
            'index' => ListGeneralStandards::route('/'),
            'create' => CreateGeneralStandard::route('/create'),
            'view' => ViewGeneralStandard::route('/{record}'),
            'edit' => EditGeneralStandard::route('/{record}/edit'),
        ];
    }
}
