<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Application;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicationsStatsWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'إحصائيات طلبات التقديم';
    protected int | string | array $columnSpan = 1;

    private function getFilterUrl(array $additionalFilters = []): string
    {
        $cleanFilters = [];
        foreach ($additionalFilters as $key => $value) {
            if ($value !== null) {
                $cleanFilters[$key] = $value;
            }
        }
        $filters = array_replace_recursive([], $cleanFilters);

        return \App\Filament\Resources\Applications\ApplicationResource::getUrl('index', [
            'filters' => $filters,
        ]);
    }

    public function table(Table $table): Table
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $studyType = $this->filters['STUDYTYPE_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $isDetailed = !empty($unid);

        return $table
            ->query(function () use ($unid, $faculty, $program, $studyType, $dateFrom, $dateTo, $isDetailed) {
                $query = Application::query()
                    ->leftJoin('university', 'applications.UNID', '=', 'university.UNID');

                if ($isDetailed) {
                    $query->leftJoin('faculty', function ($join) {
                        $join->on('applications.UNID', '=', 'faculty.UNID')
                            ->on('applications.FACULTY_IDENT', '=', 'faculty.FACULTY_IDENT');
                    })
                        ->leftJoin('programs as program', function ($join) {
                            $join->on('applications.UNID', '=', 'program.UNID')
                                ->on('applications.FACULTY_IDENT', '=', 'program.FACULTY_IDENT')
                                ->on('applications.PROGRAM_IDENT', '=', 'program.PROGRAM_IDENT');
                        })
                        ->leftJoin('study_type', function ($join) {
                            // تم إصلاح الربط هنا لمنع التكرار (إضافة شرط الجامعة)
                            $join->on('applications.UNID', '=', 'study_type.UNID')
                                ->on('applications.STUDYTYPE_IDENT', '=', 'study_type.STUDYTYPE_IDENT');
                        })
                        ->select(
                            'applications.UNID',
                            'applications.FACULTY_IDENT',
                            'applications.PROGRAM_IDENT',
                            'applications.STUDYTYPE_IDENT',
                            'university.U_NAME as UNIVERSITY_NAME',
                            'faculty.FACULTY_NAME as FACULTY_NAME',
                            'program.PROGRAM_NAME as PROGRAM_NAME',
                            'study_type.STUDYTYPE_NAME as STUDY_TYPE_NAME',
                            DB::raw('MAX(applications.APPLICATION_IDENT) as APPLICATION_IDENT'),
                            DB::raw('COUNT(*) as total_count'),
                            DB::raw('SUM(CASE WHEN applications.PAYMENT_FLAG = 1 THEN 1 ELSE 0 END) as paid_count'),
                            DB::raw('SUM(CASE WHEN applications.ACCEPTED = 1 THEN 1 ELSE 0 END) as accepted_count'),
                            DB::raw('SUM(CASE WHEN applications.CONFIRMED_BY_APPLICANT = 1 THEN 1 ELSE 0 END) as confirmed_count')
                        )
                        // تم إضافة المعرفات (IDs) للتجميع لضمان عدم تداخل البيانات
                        ->groupBy(
                            'applications.UNID',
                            'applications.FACULTY_IDENT',
                            'applications.PROGRAM_IDENT',
                            'applications.STUDYTYPE_IDENT',
                            'university.U_NAME',
                            'faculty.FACULTY_NAME',
                            'program.PROGRAM_NAME',
                            'study_type.STUDYTYPE_NAME'
                        );
                } else {
                    $query->select(
                        'applications.UNID',
                        'university.U_NAME as UNIVERSITY_NAME',
                        DB::raw('MAX(applications.APPLICATION_IDENT) as APPLICATION_IDENT'),
                        DB::raw('COUNT(*) as total_count'),
                        DB::raw('SUM(CASE WHEN applications.PAYMENT_FLAG = 1 THEN 1 ELSE 0 END) as paid_count'),
                        DB::raw('SUM(CASE WHEN applications.ACCEPTED = 1 THEN 1 ELSE 0 END) as accepted_count'),
                        DB::raw('SUM(CASE WHEN applications.CONFIRMED_BY_APPLICANT = 1 THEN 1 ELSE 0 END) as confirmed_count')
                    )
                        ->groupBy('applications.UNID', 'university.U_NAME');
                }

                $query
                    ->when($unid, fn($q) => $q->where('applications.UNID', $unid))
                    ->when($faculty, fn($q) => $q->where('applications.FACULTY_IDENT', $faculty))
                    ->when($program, fn($q) => $q->where('applications.PROGRAM_IDENT', $program))
                    ->when($studyType, fn($q) => $q->where('applications.STUDYTYPE_IDENT', $studyType))
                    ->when($dateFrom, fn($q) => $q->whereDate('applications.RECORDDATE', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('applications.RECORDDATE', '<=', $dateTo));

                return Application::query()->withoutGlobalScopes()
                    ->fromSub($query, 'applications')
                    ->orderByDesc('total_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('UNIVERSITY_NAME')
                    ->label('الجامعة')
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->visible(fn() => !empty($this->filters['UNID']))
                    ->formatStateUsing(fn($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('PROGRAM_NAME')
                    ->label('التخصص')
                    ->visible(fn() => !empty($this->filters['UNID']))
                    ->formatStateUsing(fn($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('STUDY_TYPE_NAME')
                    ->label('النظام الدراسي')
                    ->visible(fn() => !empty($this->filters['UNID']))
                    ->formatStateUsing(fn($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('الطلبات')
                    ->url(fn($record) => $record->total_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => array_filter([
                            'UNID' => $record->UNID ?? null,
                            'FACULTY_IDENT' => $record->FACULTY_IDENT ?? null,
                            'PROGRAM_IDENT' => $record->PROGRAM_IDENT ?? null,
                        ]),
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->total_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_count')
                    ->label('المسددة')
                    ->color('warning')
                    ->url(fn($record) => $record->paid_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => array_filter([
                            'UNID' => $record->UNID ?? null,
                            'FACULTY_IDENT' => $record->FACULTY_IDENT ?? null,
                            'PROGRAM_IDENT' => $record->PROGRAM_IDENT ?? null,
                        ]),
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null,
                        'is_paid' => ['value' => 'true']
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->paid_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('accepted_count')
                    ->label('المقبولة')
                    ->color('success')
                    ->url(fn($record) => $record->accepted_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => array_filter([
                            'UNID' => $record->UNID ?? null,
                            'FACULTY_IDENT' => $record->FACULTY_IDENT ?? null,
                            'PROGRAM_IDENT' => $record->PROGRAM_IDENT ?? null,
                        ]),
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null,
                        'ACCEPTED' => ['value' => 'true']
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->accepted_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('confirmed_count')
                    ->label('المؤكدة')
                    ->color('primary')
                    ->url(fn($record) => $record->confirmed_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => array_filter([
                            'UNID' => $record->UNID ?? null,
                            'FACULTY_IDENT' => $record->FACULTY_IDENT ?? null,
                            'PROGRAM_IDENT' => $record->PROGRAM_IDENT ?? null,
                        ]),
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null,
                        'CONFIRMED_BY_APPLICANT' => ['value' => 'true']
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->confirmed_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(\App\Filament\Exports\Statistics\ApplicationsStatsExporter::class)
                    ->label('تصدير إكسل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->isAdmin()
                        || auth()->user()?->can('Export:ApplicationStatistics')
                        || auth()->user()?->can('Export:Statistics')),
            ])
            ->paginated([5, 10, 25, 50, 'all'])
            ->defaultPaginationPageOption(5)
            ->summaries(false);
    }
}
