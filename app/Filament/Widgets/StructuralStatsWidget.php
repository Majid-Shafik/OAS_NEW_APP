<?php

namespace App\Filament\Widgets;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StructuralStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('عدد الجامعات', University::count())
                ->description('إجمالي الجامعات المسجلة')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('primary'),
            Stat::make('عدد الكليات', Faculty::count())
                ->description('إجمالي الكليات')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            Stat::make('عدد التخصصات', Program::count())
                ->description('إجمالي التخصصات المتاحة')
                ->descriptionIcon('heroicon-m-book-open')
                ->color('warning'),
        ];
    }
}
