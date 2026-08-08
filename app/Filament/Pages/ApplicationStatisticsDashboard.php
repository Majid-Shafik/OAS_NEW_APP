<?php

namespace App\Filament\Pages;

use App\Traits\HasDashboardFilters;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class ApplicationStatisticsDashboard extends BaseDashboard
{
    use HasFiltersForm;
    use HasDashboardFilters;

    protected static \UnitEnum|string|null $navigationGroup = 'الإحصائيات';
    protected static ?string $navigationLabel = 'إحصائيات طلبات التقديم';
    protected static ?string $title = 'إحصائيات طلبات التقديم';
    protected static string $routePath = 'application-statistics';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->can('View:ApplicationStatisticsDashboard');
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components(
            $this->getDashboardFiltersSchema()
        );
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\Statistics\ApplicationStatsOverviewWidget::class,
            \App\Filament\Widgets\Statistics\ApplicationsStatsWidget::class,
            \App\Filament\Widgets\Statistics\ApplicationsStatsChart::class,
            \App\Filament\Widgets\Statistics\ApplicantsByStudyTypeWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsByStudyTypeChart::class,
            \App\Filament\Widgets\Statistics\DetailedApplicationsStatsWidget::class,
        ];
    }
}
