<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ApplicantsBySecSchoolTypeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'توزيع المتقدمين حسب نوع الثانوية';
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $applyFilters = function ($q) use ($unid, $faculty, $program, $dateFrom, $dateTo) {
            return $q->when($unid, fn($q) => $q->where('UNID', $unid))
              ->when($faculty, fn($q) => $q->where('ADMITTED_FACULITY', $faculty))
              ->when($program, fn($q) => $q->where('ADMITTED_PROGRAM', $program))
              ->when($dateFrom, fn($q) => $q->whereDate('RECORDDATE', '>=', $dateFrom))
              ->when($dateTo, fn($q) => $q->whereDate('RECORDDATE', '<=', $dateTo));
        };

        $data = \App\Models\ComboValue::query()
            ->where('CODE', 1)
            ->withCount([
                'secSchoolApplicants as total_count' => fn($q) => $applyFilters($q),
            ])
            ->having('total_count', '>', 0)
            ->orderByDesc('total_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المتقدمين',
                    'data' => $data->pluck('total_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6', '#6366f1', '#14b8a6', '#f43f5e'
                    ],
                ],
            ],
            'labels' => $data->pluck('VALUE')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
