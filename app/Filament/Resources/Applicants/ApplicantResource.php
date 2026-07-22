<?php

namespace App\Filament\Resources\Applicants;

use App\Filament\Resources\Applicants\Pages\CreateApplicant;
use App\Filament\Resources\Applicants\Pages\EditApplicant;
use App\Filament\Resources\Applicants\Pages\ListApplicants;
use App\Filament\Resources\Applicants\Pages\ViewApplicant;
use App\Filament\Resources\Applicants\RelationManagers\ApplicationsRelationManager;
use App\Filament\Resources\Applicants\Schemas\ApplicantForm;
use App\Filament\Resources\Applicants\Schemas\ApplicantInfolist;
use App\Filament\Resources\Applicants\Tables\ApplicantsTable;
use App\Models\Applicant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ApplicantResource extends Resource
{
    protected static ?string $model = Applicant::class;

    protected static ?string $modelLabel = 'متقدم';

    protected static ?string $pluralModelLabel = 'المتقدمين';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';

    protected static ?string $recordTitleAttribute = 'FULL_NAME';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'FULL_NAME',
            'FIRST_NAME',
            'LAST_NAME',
            'APPLICANT_IDENT',
            'SEC_SCHOOL_SEATNO',
            'MOBILE_PHONE',
            'PLACE_OF_BIRTH',
            'PROVINCE',
            'TERRITORY',
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'رقم التنسيق' => $record->APPLICANT_IDENT,
            'رقم الجلوس' => $record->SEC_SCHOOL_SEATNO,
            'الهاتف' => $record->MOBILE_PHONE,
            'المحافظة/المديرية' => $record->PROVINCE.' - '.$record->TERRITORY,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ApplicantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicantInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplicants::route('/'),
            'create' => CreateApplicant::route('/create'),
            'view' => ViewApplicant::route('/{record}'),
            'edit' => EditApplicant::route('/{record}/edit'),
        ];
    }
}
