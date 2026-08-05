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

use App\Filament\Traits\HasClearingReviewActions;

class ClearingApplicantResource extends ApplicantResource
{
    use HasClearingReviewActions;
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
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        $table = parent::table($table);

        $reviewActions = self::getClearingReviewActions(
            \Filament\Actions\Action::class,
            \Filament\Actions\ActionGroup::class
        );

        return $table
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                ...$reviewActions,
            ])
            ->recordUrl(fn (\Illuminate\Database\Eloquent\Model $record): string => static::getUrl('view', ['record' => $record]));
    }
}
