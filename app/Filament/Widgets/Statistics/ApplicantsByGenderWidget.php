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

class ApplicantsByGenderWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'إحصائيات المتقدمين حسب الجنس';
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
                $applyFilters = function ($q) use ($unid, $faculty, $program, $dateFrom, $dateTo) {
                    return $q->when($unid, fn($q) => $q->where('UNID', $unid))
                      ->when($faculty, fn($q) => $q->where('ADMITTED_FACULITY', $faculty))
                      ->when($program, fn($q) => $q->where('ADMITTED_PROGRAM', $program))
                      ->when($dateFrom, fn($q) => $q->whereDate('RECORDDATE', '>=', $dateFrom))
                      ->when($dateTo, fn($q) => $q->whereDate('RECORDDATE', '<=', $dateTo));
                };

                return clone \App\Models\ComboValue::query()
                    ->where('CODE', 6)
                    ->withCount([
                        'genderApplicants as total_count' => fn($q) => $applyFilters($q),
                        'genderApplicants as updated_count' => fn($q) => $applyFilters($q)->where('STATUS', ApplicantStatus::Updated->value),
                        'genderApplicants as ready_count' => fn($q) => $applyFilters($q)->where('STATUS', ApplicantStatus::Ready->value),
                    ])
                    ->having('total_count', '>', 0)
                    ->orderByDesc('total_count');
            })
            ->columns([
                Tables\Columns\TextColumn::make('VALUE')
                    ->label('الجنس')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_count')
                    ->label('إجمالي المتقدمين')
                    ->url(fn($record) => $record->total_count > 0 ? $this->getFilterUrl(['GENDER' => ['value' => (string) $record->VALUE]]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->total_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_count')
                    ->label('مُحدّث (UPDATED)')
                    ->color('warning')
                    ->url(fn($record) => $record->updated_count > 0 ? $this->getFilterUrl([
                        'GENDER' => ['value' => (string) $record->VALUE],
                        'STATUS' => ['value' => ApplicantStatus::Updated->value]
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->updated_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
                Tables\Columns\TextColumn::make('ready_count')
                    ->label('جاهز (READY)')
                    ->color('success')
                    ->url(fn($record) => $record->ready_count > 0 ? $this->getFilterUrl([
                        'GENDER' => ['value' => (string) $record->VALUE],
                        'STATUS' => ['value' => ApplicantStatus::Ready->value]
                    ]) : null, shouldOpenInNewTab: true)
                    ->tooltip(fn($record) => $record->ready_count > 0 ? 'انقر هنا لاستعراض المتقدمين' : null)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('الاجمالي')->numeric())
                    ->sortable(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(\App\Filament\Exports\Statistics\ApplicantsByGenderExporter::class)
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
