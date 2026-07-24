<?php

namespace App\Filament\Pages;

use App\Traits\HasDashboardFilters;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;

class ApplicantStatisticsDashboard extends BaseDashboard
{
    use HasFiltersForm;
    use HasDashboardFilters;

    protected static \UnitEnum|string|null $navigationGroup = 'الإحصائيات';
    protected static ?string $navigationLabel = 'إحصائيات المتقدمين';
    protected static ?string $title = 'إحصائيات المتقدمين';
    protected static string $routePath = 'applicant-statistics';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components(
            $this->getDashboardFiltersSchema()
        );
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\Statistics\ApplicantStatsOverviewWidget::class,

            \App\Filament\Widgets\Statistics\ApplicantsByGenderWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsByGenderChart::class,
            \App\Filament\Widgets\Statistics\ApplicantsBySecSchoolTypeWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsBySecSchoolTypeChart::class,
            // 
            // 
            \App\Filament\Widgets\Statistics\ApplicantsByProvinceWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsByProvinceChart::class,
            \App\Filament\Widgets\Statistics\ApplicantsBySecProvinceWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsBySecProvinceChart::class,
            // 
            // 
            \App\Filament\Widgets\Statistics\ApplicantsByUniversityFacultyWidget::class,
            \App\Filament\Widgets\Statistics\ApplicantsByUniversityFacultyChart::class,
        ];
    }
}
