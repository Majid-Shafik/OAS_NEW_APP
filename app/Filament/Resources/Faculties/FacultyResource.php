<?php

namespace App\Filament\Resources\Faculties;

use App\Filament\Resources\Faculties\Pages\CreateFaculty;
use App\Filament\Resources\Faculties\Pages\EditFaculty;
use App\Filament\Resources\Faculties\Pages\ListFaculties;
use App\Filament\Resources\Faculties\Pages\ViewFaculty;
use App\Filament\Resources\Faculties\Schemas\FacultyForm;
use App\Filament\Resources\Faculties\Schemas\FacultyInfolist;
use App\Filament\Resources\Faculties\Tables\FacultiesTable;
use App\Models\Faculty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static ?string $modelLabel = 'كلية';

    protected static ?string $pluralModelLabel = 'الكليات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static UnitEnum|string|null $navigationGroup = 'الكليات والبرامج';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'FACULTY_NAME';

    public static function form(Schema $schema): Schema
    {
        return FacultyForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FacultyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacultiesTable::configure($table);
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
            'index' => ListFaculties::route('/'),
            'create' => CreateFaculty::route('/create'),
            'view' => ViewFaculty::route('/{record}'),
            'edit' => EditFaculty::route('/{record}/edit'),
        ];
    }
}
