<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ApplicantsByUniversityFacultyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'توزيع المتقدمين حسب الكليات';
    protected int | string | array $columnSpan = 1;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $data = clone Applicant::query()
            ->leftJoin('faculty', function ($join) {
                $join->on('applicant.UNID', '=', 'faculty.UNID')
                     ->on('applicant.ADMITTED_FACULITY', '=', 'faculty.FACULTY_IDENT');
            })
            ->select('faculty.FACULTY_NAME as FACULTY_NAME', DB::raw('COUNT(*) as total_count'))
            ->when($unid, fn($q) => $q->where('applicant.UNID', $unid))
            ->when($faculty, fn($q) => $q->where('applicant.ADMITTED_FACULITY', $faculty))
            ->when($program, fn($q) => $q->where('applicant.ADMITTED_PROGRAM', $program))
            ->when($dateFrom, fn($q) => $q->whereDate('applicant.RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('applicant.RECORDDATE', '<=', $dateTo))
            ->groupBy('faculty.FACULTY_NAME')
            ->orderByDesc('total_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المتقدمين',
                    'data' => $data->pluck('total_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'
                    ],
                ],
            ],
            'labels' => $data->map(fn ($r) => empty($r->FACULTY_NAME) ? 'غير محدد' : $r->FACULTY_NAME)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
