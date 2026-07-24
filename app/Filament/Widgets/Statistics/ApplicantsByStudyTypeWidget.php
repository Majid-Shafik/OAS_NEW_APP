<?php

namespace App\Filament\Widgets\Statistics;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use App\Models\StudyType;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicantsByStudyTypeWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'إحصائيات طلبات التقديم حسب النظام الدراسي';
    protected int | string | array $columnSpan = 1;

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

        $filters = array_replace_recursive($filters, $additionalFilters);

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

        return $table
            ->query(function () use ($unid, $faculty, $program, $studyType, $dateFrom, $dateTo) {
                $query = \App\Models\Application::query()
                    ->leftJoin('study_type', function ($join) {
                        $join->on('applications.UNID', '=', 'study_type.UNID')
                             ->on('applications.STUDYTYPE_IDENT', '=', 'study_type.STUDYTYPE_IDENT');
                    })
                    ->select('applications.STUDYTYPE_IDENT', 'study_type.STUDYTYPE_NAME as STUDY_TYPE_NAME', 
                        DB::raw('MAX(applications.APPLICATION_IDENT) as APPLICATION_IDENT'),
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
                    ->groupBy('applications.STUDYTYPE_IDENT', 'study_type.STUDYTYPE_NAME');

                return \App\Models\Application::query()->withoutGlobalScopes()
                    ->fromSub($query, 'applications')
                    ->orderByDesc('total_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('STUDY_TYPE_NAME')
                    ->label('النظام الدراسي')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => empty($state) ? 'غير محدد' : $state),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('إجمالي الطلبات')
                    ->url(fn($record) => $record->total_count > 0 ? $this->getFilterUrl([
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->total_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_count')
                    ->label('المسددة')
                    ->color('warning')
                    ->url(fn($record) => $record->paid_count > 0 ? $this->getFilterUrl([
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
                        'STUDYTYPE_IDENT' => !empty($record->STUDYTYPE_IDENT) ? ['value' => (string) $record->STUDYTYPE_IDENT] : null,
                        'CONFIRMED_BY_APPLICANT' => ['value' => 'true']
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->confirmed_count > 0 ? 'انقر هنا لاستعراض الطلبات' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(\App\Filament\Exports\Statistics\ApplicantsByStudyTypeExporter::class)
                    ->label('تصدير إكسل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success'),
            ])
            ->paginated([5, 10, 25, 50, 'all'])
            ->defaultPaginationPageOption(5)
            ->summaries(false);
    }
}
