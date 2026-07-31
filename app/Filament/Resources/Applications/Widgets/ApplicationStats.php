<?php

namespace App\Filament\Resources\Applications\Widgets;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي طلبات التقديم', Application::count())
                ->description('كافة الرغبات المسجلة')
                ->descriptionIcon('heroicon-m-document-duplicate')
                ->color('primary'),
            Stat::make('الطلبات المسددة', Application::whereNotNull('PAYMENT_FLAG')->where('PAYMENT_FLAG', '!=', 0)->count())
                ->description('تم تسديد الرسوم')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
            Stat::make('الطلبات المقبولة', Application::where('ACCEPTED', 1)->count())
                ->description('المقبولين مبدئياً')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make('الطلبات المؤكدة', Application::where('CONFIRMED_BY_APPLICANT', 1)->count())
                ->description('الذين أكدوا قبولهم')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
