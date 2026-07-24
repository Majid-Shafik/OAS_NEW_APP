<?php

namespace App\Filament\Exports\Statistics;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ClearingApplicationsStatsExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('UNIVERSITY_NAME')->label('الجامعة'),
            ExportColumn::make('FACULTY_NAME')->label('الكلية'),
            ExportColumn::make('PROGRAM_NAME')->label('التخصص'),
            ExportColumn::make('STUDY_TYPE_NAME')->label('النظام الدراسي'),
            ExportColumn::make('total_count')->label('إجمالي المتقدمين'),
            ExportColumn::make('paid_count')->label('مسدد رسوم'),
            ExportColumn::make('accepted_count')->label('مقبول'),
            ExportColumn::make('confirmed_count')->label('مؤكد'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'لقد اكتمل تصدير الإحصائيات وجاهز للتحميل.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' هناك ' . number_format($failedRowsCount) . ' صف فشل تصديره.';
        }

        return $body;
    }
}
