<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Widgets;

use App\Models\HighSchoolDegreeBType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HighSchoolDegreeBTypeStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('عدد المتقدمين (نوع B)', HighSchoolDegreeBType::count())
                ->description('إجمالي المتقدمين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('المعتمدين', HighSchoolDegreeBType::where('APPROVED', 1)->count())
                ->description('إجمالي المعتمدين')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('غير المعتمدين', HighSchoolDegreeBType::where(function($query) {
                    $query->whereNull('APPROVED')->orWhere('APPROVED', 0);
                })->count())
                ->description('بانتظار الاعتماد أو مرفوضين')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
