<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المتقدمين', Applicant::count())
                ->description('الطلاب المسجلين بالنظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('طلبات التقديم (الرغبات)', Application::count())
                ->description('إجمالي الرغبات المدخلة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('المقبولين', Application::where('ACCEPTED', 1)->count())
                ->description('الطلاب الذين تم قبولهم')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('المؤكدين', Application::where('CONFIRMED_BY_APPLICANT', 1)->count())
                ->description('الطلاب المؤكدين نهائياً')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('warning'),
        ];
    }
}
