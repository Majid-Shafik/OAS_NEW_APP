<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string | Htmlable | null
    {
        $currentDb = session('tenant_database', config('academic_years.default_database'));
        $databases = config('academic_years.databases', []);
        $yearLabel = $databases[$currentDb] ?? $currentDb;

        return new HtmlString(
            '<div style="display: inline-flex; align-items: center; gap: 12px; flex-wrap: wrap;">' .
                '<span style="font-size: 1.5rem; font-weight: 700;">لوحة التحكم</span>' .
                '<span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; font-size: 0.875rem; font-weight: 600; border-radius: 9999px; background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">' .
                    '📅 بوابة قبول عام: ' . e($yearLabel) .
                '</span>' .
            '</div>'
        );
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
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
            \App\Filament\Widgets\CategoriesStatsWidget::class,
        ];
    }
}
