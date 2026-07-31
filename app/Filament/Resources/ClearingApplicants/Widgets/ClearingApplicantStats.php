<?php

namespace App\Filament\Resources\ClearingApplicants\Widgets;

use App\Models\ClearingApplicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClearingApplicantStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('بانتظار المراجعة الأولى', ClearingApplicant::where(fn($q) => $q->whereNull('REVIEWED')->orWhere('REVIEWED', 0))->count())
                ->description('تحتاج مراجعة أولية')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('بانتظار المراجعة الثانية', ClearingApplicant::where('REVIEWED', 1)->where(fn($q) => $q->whereNull('SECOND_REVIEWED')->orWhere('SECOND_REVIEWED', 0))->count())
                ->description('تحتاج تدقيق نهائي')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('info'),

            Stat::make('مرفوض', ClearingApplicant::where('REVIEWED', 2)->orWhere('SECOND_REVIEWED', 2)->count())
                ->description('مرفوضة في إحدى المراحل')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('معتمد نهائياً', ClearingApplicant::where('REVIEWED', 1)->where('SECOND_REVIEWED', 1)->count())
                ->description('تم اعتمادها')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
