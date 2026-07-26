<?php

namespace App\Filament\Pages;

use App\Traits\HasDashboardFilters;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class ClearingStatisticsDashboard extends BaseDashboard
{
    use HasFiltersForm;
    use HasDashboardFilters;

    protected static \UnitEnum|string|null $navigationGroup = 'المقاصاة';
    protected static ?string $navigationLabel = 'إحصائيات طلاب المقاصاة';
    protected static ?string $title = 'إحصائيات طلاب المقاصاة';

    protected static ?int $navigationSort = 100;
    protected static string $routePath = 'clearing-statistics';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-academic-cap';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components(
            $this->getDashboardFiltersSchema()
        );
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\Statistics\ClearingApplicationsStatsWidget::class,
            \App\Filament\Widgets\Statistics\ClearingApplicationsStatsBarChart::class,
            \App\Filament\Widgets\Statistics\ClearingApplicationsStatsPieChart::class,
        ];
    }
}
