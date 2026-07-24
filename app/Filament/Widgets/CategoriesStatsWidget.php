<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoriesStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            Stat::make('عدد الجامعات', University::Coordination()->count())
                ->description('إجمالي الجامعات المسجلة')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('warning'),
            Stat::make('عدد الكليات', Faculty::count())
                ->description('إجمالي الكليات')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('عدد التخصصات', Program::count())
                ->description('إجمالي التخصصات المتاحة')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
                
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
                
            Stat::make('طلاب الفئة A و A*', Applicant::whereIn('APPLICANT_TYPE', [1, 3])->count())
                ->description('المتقدمين من الفئة A (بنوعيها)')
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
