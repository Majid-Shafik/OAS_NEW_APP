<?php

namespace App\Filament\Widgets\Statistics;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicantsByUniversityFacultyWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'إحصائيات المتقدمين حسب الجامعة والكلية';
    protected int | string | array $columnSpan = 1;

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

    public function table(Table $table): Table
    {
        $unid = $this->filters['UNID'] ?? null;
        $faculty = $this->filters['FACULTY_IDENT'] ?? null;
        $program = $this->filters['PROGRAM_IDENT'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        return $table
            ->query(function () use ($unid, $faculty, $program, $dateFrom, $dateTo) {
                $query = Applicant::query()
                    ->leftJoin('university', 'applicant.UNID', '=', 'university.UNID')
                    ->leftJoin('faculty', function ($join) {
                        $join->on('applicant.UNID', '=', 'faculty.UNID')
                             ->on('applicant.ADMITTED_FACULITY', '=', 'faculty.FACULTY_IDENT');
                    })
                    ->select('applicant.UNID', 'applicant.ADMITTED_FACULITY as FACULTY_IDENT', 'university.U_NAME as UNIVERSITY_NAME', 'faculty.FACULTY_NAME as FACULTY_NAME', 
                        DB::raw('MAX(applicant.APPLICANT_IDENT) as APPLICANT_IDENT'),
                        DB::raw('COUNT(*) as total_count'),
                        DB::raw('SUM(CASE WHEN applicant.STATUS = "' . ApplicantStatus::Updated->value . '" THEN 1 ELSE 0 END) as updated_count'),
                        DB::raw('SUM(CASE WHEN applicant.STATUS = "' . ApplicantStatus::Ready->value . '" THEN 1 ELSE 0 END) as ready_count')
                    )
                    ->when($unid, fn($q) => $q->where('applicant.UNID', $unid))
                    ->when($faculty, fn($q) => $q->where('applicant.ADMITTED_FACULITY', $faculty))
                    ->when($program, fn($q) => $q->where('applicant.ADMITTED_PROGRAM', $program))
                    ->when($dateFrom, fn($q) => $q->whereDate('applicant.RECORDDATE', '>=', $dateFrom))
                    ->when($dateTo, fn($q) => $q->whereDate('applicant.RECORDDATE', '<=', $dateTo))
                    ->groupBy('applicant.UNID', 'applicant.ADMITTED_FACULITY', 'university.U_NAME', 'faculty.FACULTY_NAME');

                return Applicant::query()->withoutGlobalScopes()
                    ->fromSub($query, 'applicant')
                    ->orderBy('UNIVERSITY_NAME')
                    ->orderBy('FACULTY_NAME')
                    ->orderByDesc('total_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('UNIVERSITY_NAME')
                    ->label('الجامعة')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->formatStateUsing(fn ($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('المتقدمين')
                    ->url(fn($record) => $record->total_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => [
                            'UNID' => $record->UNID,
                            'ADMITTED_FACULITY' => $record->FACULTY_IDENT,
                        ]
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->total_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_count')
                    ->label('مُحدّث (UPDATED)')
                    ->color('warning')
                    ->url(fn($record) => $record->updated_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => [
                            'UNID' => $record->UNID,
                            'ADMITTED_FACULITY' => $record->FACULTY_IDENT,
                        ],
                        'STATUS' => ['value' => ApplicantStatus::Updated->value]
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->updated_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('ready_count')
                    ->label('جاهز (READY)')
                    ->color('success')
                    ->url(fn($record) => $record->ready_count > 0 ? $this->getFilterUrl([
                        'university_faculty_program' => [
                            'UNID' => $record->UNID,
                            'ADMITTED_FACULITY' => $record->FACULTY_IDENT,
                        ],
                        'STATUS' => ['value' => ApplicantStatus::Ready->value]
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->ready_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(\App\Filament\Exports\Statistics\ApplicantsByUniversityFacultyExporter::class)
                    ->label('تصدير إكسل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->isAdmin()
                        || auth()->user()?->can('Export:ApplicantStatistics')
                        || auth()->user()?->can('Export:Statistics')),
            ])
            ->paginated([5, 10, 25, 50, 'all'])
            ->defaultPaginationPageOption(5)
            ->summaries(false);
    }
}
