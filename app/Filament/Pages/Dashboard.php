<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components(
            $this->getDashboardFiltersSchema()
        );
    }
    public function getWidgets(): array
    {
        return [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
            \App\Filament\Widgets\CategoriesStatsWidget::class,
        ];
    }
}
