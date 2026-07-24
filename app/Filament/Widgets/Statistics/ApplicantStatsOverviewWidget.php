<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Applicant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ApplicantStatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    private function getFilterUrl(array $additionalFilters = []): string
    {
        $filters = [];
        if (!empty($this->filters['UNID'])) {
            $filters['university_faculty_program']['UNID'] = $this->filters['UNID'];
        }
        if (!empty($this->filters['FACULTY_IDENT'])) {
            $filters['university_faculty_program']['ADMITTED_FACULITY'] = $this->filters['FACULTY_IDENT'];
        }
        if (!empty($this->filters['PROGRAM_IDENT'])) {
            $filters['university_faculty_program']['ADMITTED_PROGRAM'] = $this->filters['PROGRAM_IDENT'];
        }

        $filters = array_replace_recursive($filters, $additionalFilters);

        return \App\Filament\Resources\Applicants\ApplicantResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }

    protected function getStats(): array
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $baseQuery = Applicant::query()
            ->when($unid, fn($q) => $q->where('UNID', $unid))
            ->when($faculty, fn($q) => $q->where('ADMITTED_FACULITY', $faculty))
            ->when($program, fn($q) => $q->where('ADMITTED_PROGRAM', $program))
            ->when($dateFrom, fn($q) => $q->whereDate('RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('RECORDDATE', '<=', $dateTo));

        return [
            Stat::make('إجمالي المتقدمين', (clone $baseQuery)->count())
                ->description('إجمالي عدد المتقدمين المسجلين')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->url($this->getFilterUrl()),
            Stat::make('متقدمي الفئة A و A*', (clone $baseQuery)->whereIn('APPLICANT_TYPE', [1, 3])->count())
                ->description('المتقدمين من الفئة A (بنوعيها)')
                ->descriptionIcon('heroicon-m-star')
                ->color('success')
                ->url($this->getFilterUrl(['APPLICANT_TYPE' => ['value' => '1']])),
            Stat::make('متقدمي الفئة B', (clone $baseQuery)->where('APPLICANT_TYPE', 2)->count())
                ->description('المتقدمين من الفئة B')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning')
                ->url($this->getFilterUrl(['APPLICANT_TYPE' => ['value' => '2']])),
            Stat::make('متقدمي المقاصة', (clone $baseQuery)->where('IS_CLEARING', 1)->count())
                ->description('المتقدمين المحولين بنظام المقاصة')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('danger')
                ->url($this->getFilterUrl(['IS_CLEARING' => ['value' => '1']])),
        ];
    }
}
