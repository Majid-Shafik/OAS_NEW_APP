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

    protected static ?string $modelLabel = 'متقدمين';

    protected static ?string $pluralModelLabel = 'المتقدمين';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';

    protected static ?string $recordTitleAttribute = 'FULL_NAME';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'FULL_NAME',          
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
            'عام التخرج' => $record->SEC_SCHOOL_YEAR,
            'الهاتف' => $record->MOBILE_PHONE,
            'الجامعة' => $record->university->U_NAME ?? '',
            // 'المحافظة/المديرية' => $record->PROVINCE . ' - ' . $record->TERRITORY,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return $record->getProfileUrl();
    }

    public static function form(Schema $schema): Schema
    {
        if ($schema->getLivewire() instanceof \Filament\Resources\Pages\EditRecord) {
            return \App\Filament\Resources\Applicants\Schemas\ApplicantEditForm::configure($schema);
        }
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

    public static function resolveRecordRouteBinding(int | string $key, ?\Closure $modifyQuery = null): ?Model
    {
        $query = static::getRecordRouteBindingEloquentQuery();

        if ($modifyQuery) {
            $query = $modifyQuery($query) ?? $query;
        }

        if (is_string($key) && str_contains($key, '_')) {
            $parts = explode('_', $key);
            $modelInstance = app(static::getModel());
            $table = $modelInstance->getTable();

            if (auth()->check() && auth()->user()->UNID == 0) {
                $query->withoutGlobalScope(\App\Models\Scopes\UniversityScope::class);
            }

            return $query->where("{$table}.UNID", $parts[0])
                ->where("{$table}.APPLICANT_IDENT", $parts[1])
                ->first();
        }

        return app(static::getModel())
            ->resolveRouteBindingQuery($query, $key, static::getRecordRouteKeyName())
            ->first();
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        if (static::class === self::class) {
            $query->where(function ($q) {
                $q->where('IS_CLEARING', '!=', 1)->orWhereNull('IS_CLEARING');
            });
        }
        return $query;
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
