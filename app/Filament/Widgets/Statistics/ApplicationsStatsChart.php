<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ApplicationsStatsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'مقارنة: المتقدمين / المسددين / المقبولين / المؤكدين';
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $studyType = $this->filters['STUDYTYPE_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $data = clone Application::query()
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(CASE WHEN applications.PAYMENT_FLAG = 1 THEN 1 ELSE 0 END) as paid_count'),
                DB::raw('SUM(CASE WHEN applications.ACCEPTED = 1 THEN 1 ELSE 0 END) as accepted_count'),
                DB::raw('SUM(CASE WHEN applications.CONFIRMED_BY_APPLICANT = 1 THEN 1 ELSE 0 END) as confirmed_count')
            )
            ->when($unid, fn($q) => $q->where('applications.UNID', $unid))
            ->when($faculty, fn($q) => $q->where('applications.FACULTY_IDENT', $faculty))
            ->when($program, fn($q) => $q->where('applications.PROGRAM_IDENT', $program))
            ->when($studyType, fn($q) => $q->where('applications.STUDYTYPE_IDENT', $studyType))
            ->when($dateFrom, fn($q) => $q->whereDate('applications.RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('applications.RECORDDATE', '<=', $dateTo))
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'الأعداد',
                    'data' => [
                        // $data->total_count ?? 0,
                        $data->paid_count ?? 0,
                        $data->accepted_count ?? 0,
                        $data->confirmed_count ?? 0,
                    ],
                    'backgroundColor' => [
                        // '#3b82f6', // blue (total)
                        '#f59e0b', // yellow (paid)
                        '#10b981', // green (accepted)
                        '#8b5cf6', // purple (confirmed)
                    ],
                ],
            ],
            'labels' => ['المسددين', 'المقبولين', 'المؤكدين'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
