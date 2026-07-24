<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ApplicantsByStudyTypeChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'توزيع طلبات التقديم حسب النظام الدراسي';
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

        $data = clone \App\Models\Application::query()
            ->leftJoin('study_type', 'applications.STUDYTYPE_IDENT', '=', 'study_type.STUDYTYPE_IDENT')
            ->select('study_type.STUDYTYPE_NAME as STUDY_TYPE_NAME', DB::raw('COUNT(*) as total_count'))
            ->when($unid, fn($q) => $q->where('applications.UNID', $unid))
            ->when($faculty, fn($q) => $q->where('applications.FACULTY_IDENT', $faculty))
            ->when($program, fn($q) => $q->where('applications.PROGRAM_IDENT', $program))
            ->when($studyType, fn($q) => $q->where('applications.STUDYTYPE_IDENT', $studyType))
            ->when($dateFrom, fn($q) => $q->whereDate('applications.RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('applications.RECORDDATE', '<=', $dateTo))
            ->groupBy('study_type.STUDYTYPE_NAME')
            ->orderByDesc('total_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'طلبات التقديم',
                    'data' => $data->pluck('total_count')->toArray(),
                    'backgroundColor' => [
                        '#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6'
                    ],
                ],
            ],
            'labels' => $data->map(fn ($r) => empty($r->STUDY_TYPE_NAME) ? 'غير محدد' : $r->STUDY_TYPE_NAME)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
