<?php

namespace App\Filament\Resources\Applications;

use App\Filament\Resources\Applications\Pages\CreateApplication;
use App\Filament\Resources\Applications\Pages\EditApplication;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Filament\Resources\Applications\Pages\ViewApplication;
use App\Filament\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';



    protected static ?string $recordTitleAttribute = 'APPLICATION_IDENT';

    public static function getModelLabel(): string
    {
        return 'طلبات التقديم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'طلبات التقديم';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'APPLICATION_IDENT',
            'applicant.FULL_NAME',
            'applicant.MOBILE_PHONE',
            'applicant.SEC_SCHOOL_SEATNO',
            'applicant.APPLICANT_IDENT',
        ];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'رقم الطلب' => $record->APPLICATION_IDENT,
            'اسم المتقدم' => $record->applicant?->FULL_NAME,
            'رقم التنسيق' => $record->applicant?->APPLICANT_IDENT,
            'الهاتف' => $record->applicant?->MOBILE_PHONE,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApplicationInfolist::configure($schema);
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
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
