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
            Stat::make('إجمالي الطلبات', Application::count())
                ->description('إجمالي طلبات التقديم المرفوعة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('الطلبات المدفوعة', Application::where('PAYMENT_FLAG', 1)->count())
                ->description('طلبات التقديم التي تم سداد رسومها')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('الطلبات المقبولة', Application::where('ACCEPTED', 1)->count())
                ->description('طلبات التقديم المقبولة نهائياً')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
