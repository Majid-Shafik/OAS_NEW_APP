<?php

namespace App\Filament\Resources\Offerings;

use App\Filament\Filters\AcademicFilter;
use App\Filament\Resources\Offerings\RelationManagers\OfferingDhsRelationManager;
use App\Filament\Resources\Offerings\RelationManagers\RequestAdjustOfferingsRelationManager;
use App\Filament\Resources\Offerings\Pages\ManageOfferings;
use App\Filament\Resources\Offerings\Pages\ViewOffering;
use App\Models\Faculty;
use App\Models\Offering;
use App\Models\Program;
use App\Models\StudyType;
use App\Models\University;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OfferingResource extends Resource
{
    protected static ?string $model = Offering::class;


    protected static UnitEnum|string|null $navigationGroup = 'المعايير';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'معيار';

    protected static ?string $pluralModelLabel = 'المعايير';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $recordTitleAttribute = 'OFFERING_IDENT';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Offerings\Schemas\OfferingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return \App\Filament\Resources\Offerings\Schemas\OfferingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Offerings\Tables\OfferingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OfferingDhsRelationManager::class,
            RequestAdjustOfferingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOfferings::route('/'),
            'view' => ViewOffering::route('/{record}'),
        ];
    }
}
