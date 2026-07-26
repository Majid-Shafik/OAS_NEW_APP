<?php

namespace App\Filament\Resources\ClearingApplicants;

use App\Filament\Resources\Applicants\ApplicantResource;
use App\Filament\Resources\ClearingApplicants\Pages;
use App\Filament\Resources\ClearingApplicants\RelationManagers;
use App\Models\ClearingApplicant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ClearingApplicantResource extends ApplicantResource
{
    protected static ?string $model = ClearingApplicant::class;
        protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'clearing-applicants';

    protected static ?string $modelLabel = 'طالب مقاصة';

    protected static ?string $pluralModelLabel = 'طلاب المقاصاة';

    protected static ?string $navigationLabel = 'طلاب المقاصاة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static UnitEnum|string|null $navigationGroup = 'المقاصاة';

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClearingApplicants::route('/'),
            'create' => Pages\CreateClearingApplicant::route('/create'),
            'view' => Pages\ViewClearingApplicant::route('/{record}'),
            'edit' => Pages\EditClearingApplicant::route('/{record}/edit'),
        ];
    }
    
    public static function getRelations(): array
    {
        return array_merge(parent::getRelations(), [
            RelationManagers\ApplicationsClearingRelationManager::class,
        ]);
    }
}
