<?php

namespace App\Filament\Resources\ProgramCapacities;

use App\Filament\Resources\ProgramCapacities\Pages\CreateProgramCapacity;
use App\Filament\Resources\ProgramCapacities\Pages\EditProgramCapacity;
use App\Filament\Resources\ProgramCapacities\Pages\ListProgramCapacities;
use App\Filament\Resources\ProgramCapacities\Pages\ViewProgramCapacity;
use App\Filament\Resources\ProgramCapacities\Schemas\ProgramCapacityForm;
use App\Filament\Resources\ProgramCapacities\Schemas\ProgramCapacityInfolist;
use App\Filament\Resources\ProgramCapacities\Tables\ProgramCapacitiesTable;
use App\Models\ProgramCapacity;
use App\Filament\Resources\ProgramCapacities\RelationManagers\HistoriesRelationManager;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProgramCapacityResource extends Resource
{
    protected static ?string $model = ProgramCapacity::class;

    protected static ?string $modelLabel = 'طاقة استيعابية';

    protected static ?string $pluralModelLabel = 'الطاقات الاستيعابية';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static UnitEnum|string|null $navigationGroup = 'المعايير';

    public static function form(Schema $schema): Schema
    {
        return ProgramCapacityForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProgramCapacityInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramCapacitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProgramCapacities::route('/'),
            'create' => CreateProgramCapacity::route('/create'),
            'view' => ViewProgramCapacity::route('/{record}'),
            'edit' => EditProgramCapacity::route('/{record}/edit'),
        ];
    }
}
