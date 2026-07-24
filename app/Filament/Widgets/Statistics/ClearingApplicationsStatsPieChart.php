<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Application;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ClearingApplicationsStatsPieChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'نسبة المتقدمين للمقاصة';
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

        $isDetailed = !empty($unid);

        $dataQuery = clone Application::query()
            ->join('applicant', function ($join) {
                $join->on('applications.APPLICANT_IDENT', '=', 'applicant.APPLICANT_IDENT')
                     ->on('applications.UNID', '=', 'applicant.UNID')
                     ->where('applicant.IS_CLEARING', 1);
            });

        if ($isDetailed) {
            $dataQuery->leftJoin('faculty', function ($join) {
                $join->on('applications.UNID', '=', 'faculty.UNID')
                     ->on('applications.FACULTY_IDENT', '=', 'faculty.FACULTY_IDENT');
            })
            ->select(
                'faculty.FACULTY_NAME as LABEL_NAME',
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('applications.FACULTY_IDENT', 'faculty.FACULTY_NAME');
        } else {
            $dataQuery->leftJoin('university', 'applications.UNID', '=', 'university.UNID')
            ->select(
                'university.U_NAME as LABEL_NAME',
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('applications.UNID', 'university.U_NAME');
        }

        $data = $dataQuery
            ->when($unid, fn($q) => $q->where('applications.UNID', $unid))
            ->when($faculty, fn($q) => $q->where('applications.FACULTY_IDENT', $faculty))
            ->when($program, fn($q) => $q->where('applications.PROGRAM_IDENT', $program))
            ->when($studyType, fn($q) => $q->where('applications.STUDYTYPE_IDENT', $studyType))
            ->when($dateFrom, fn($q) => $q->whereDate('applications.RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('applications.RECORDDATE', '<=', $dateTo))
            ->orderByDesc('total_count')
            ->limit(5)
            ->get();

        $labels = $data->pluck('LABEL_NAME')->map(fn($name) => empty($name) ? 'غير محدد' : $name)->toArray();
        $counts = $data->pluck('total_count')->toArray();

        // Generate some nice colors for the pie chart
        $colors = [
            '#3b82f6',
            '#f59e0b',
            '#10b981',
            '#8b5cf6',
            '#ef4444',
            '#06b6d4',
            '#84cc16',
            '#f43f5e',
            '#6366f1',
            '#14b8a6'
        ];

        return [
            'datasets' => [
                [
                    'label' => 'إجمالي المتقدمين للمقاصة',
                    'data' => $counts,
                    'backgroundColor' => array_slice($colors, 0, count($counts) > 0 ? count($counts) : 1),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
