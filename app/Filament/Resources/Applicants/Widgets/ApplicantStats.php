<?php

namespace App\Filament\Resources\Applicants\Widgets;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class ApplicantStats extends BaseWidget
{
    protected function getStats(): array
    {
        $baseQuery = Applicant::where(function (Builder $query) {
            $query->where('IS_CLEARING', '!=', 1)->orWhereNull('IS_CLEARING');
        });

        return [
            Stat::make('إجمالي المتقدمين', (clone $baseQuery)->count())
                ->description('المسجلين في البوابة')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('ملفات جديدة', (clone $baseQuery)->where('STATUS', ApplicantStatus::New)->count())
                ->description('تحتاج إلى مراجعة')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
            Stat::make('جاهزة للاعتماد', (clone $baseQuery)->where('STATUS', ApplicantStatus::Ready)->count())
                ->description('مكتملة وجاهزة')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('ملفات قيد التعديل', (clone $baseQuery)->where('STATUS', ApplicantStatus::Updated)->count())
                ->description('تم إرجاعها للطالب')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger'),
        ];
    }
}
