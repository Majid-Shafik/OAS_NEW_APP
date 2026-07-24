<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ApplicantsByProvinceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'توزيع المتقدمين حسب المحافظة';
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
            ->select('PROVINCE', DB::raw('COUNT(*) as total_count'))
            ->when($unid, fn($q) => $q->where('UNID', $unid))
            ->when($faculty, fn($q) => $q->where('ADMITTED_FACULITY', $faculty))
            ->when($program, fn($q) => $q->where('ADMITTED_PROGRAM', $program))
            ->when($dateFrom, fn($q) => $q->whereDate('RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('RECORDDATE', '<=', $dateTo))
            ->groupBy('PROVINCE')
            ->orderByDesc('total_count')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المتقدمين',
                    'data' => $data->pluck('total_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16', '#64748b'
                    ],
                ],
            ],
            'labels' => $data->map(fn ($r) => empty($r->PROVINCE) ? 'غير محدد' : $r->PROVINCE)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
