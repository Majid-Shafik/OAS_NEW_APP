<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoriesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('طلاب الفئة A', Applicant::where('APPLICANT_TYPE', 1)->count())
                ->description('المتقدمين من الفئة A')
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),
            Stat::make('طلاب الفئة B', Applicant::where('APPLICANT_TYPE', 2)->count())
                ->description('المتقدمين من الفئة B')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make('طلاب المقاصة', Applicant::where('IS_CLEARING', 1)->count())
                ->description('الطلاب المحولين بنظام المقاصة')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('danger'),
        ];
    }
}
