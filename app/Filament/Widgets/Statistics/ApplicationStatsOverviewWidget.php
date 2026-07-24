<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class ApplicationStatsOverviewWidget extends BaseWidget
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
            $filters['university_faculty_program']['FACULTY_IDENT'] = $this->filters['FACULTY_IDENT'];
        }
        if (!empty($this->filters['PROGRAM_IDENT'])) {
            $filters['university_faculty_program']['PROGRAM_IDENT'] = $this->filters['PROGRAM_IDENT'];
        }
        if (!empty($this->filters['STUDYTYPE_IDENT'])) {
            $filters['STUDYTYPE_IDENT'] = ['value' => (string) $this->filters['STUDYTYPE_IDENT']];
        }

        $filters = array_replace_recursive($filters, $additionalFilters);

        return \App\Filament\Resources\Applications\ApplicationResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }

    protected function getStats(): array
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $studyType = $this->filters['STUDYTYPE_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $baseQuery = Application::query()
            ->when($unid, fn($q) => $q->where('UNID', $unid))
            ->when($faculty, fn($q) => $q->where('FACULTY_IDENT', $faculty))
            ->when($program, fn($q) => $q->where('PROGRAM_IDENT', $program))
            ->when($studyType, fn($q) => $q->where('STUDYTYPE_IDENT', $studyType))
            ->when($dateFrom, fn($q) => $q->whereDate('RECORDDATE', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('RECORDDATE', '<=', $dateTo));

        return [
            Stat::make('إجمالي الطلبات', (clone $baseQuery)->count())
                ->description('إجمالي طلبات التقديم المرفوعة')
                ->descriptionIcon('heroicon-m-document-text')
                ->url($this->getFilterUrl())
                ->color('primary'),
            Stat::make('الطلبات المدفوعة', (clone $baseQuery)->where('PAYMENT_FLAG', 1)->count())
                ->description('طلبات التقديم التي تم سداد رسومها')
                ->descriptionIcon('heroicon-m-banknotes')
                ->url($this->getFilterUrl(['is_paid' => ['value' => 'true']]))
                ->color('success'),
            Stat::make('الطلبات المقبولة', (clone $baseQuery)->where('ACCEPTED', 1)->count())
                ->description('طلبات التقديم المقبولة نهائياً')
                ->descriptionIcon('heroicon-m-check-badge')
                ->url($this->getFilterUrl(['ACCEPTED' => ['value' => 'true']]))
                ->color('success'),
            Stat::make('الطلبات المؤكدة', (clone $baseQuery)->where('CONFIRMED_BY_APPLICANT', 1)->count())
                ->description('طلبات التقديم التي أكدها الطالب')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->url($this->getFilterUrl(['CONFIRMED_BY_APPLICANT' => ['value' => 'true']]))
                ->color('warning'),
        ];
    }
}
