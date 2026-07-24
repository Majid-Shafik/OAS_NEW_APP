<?php

namespace App\Filament\Exports\Statistics;

use App\Models\Application;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ApplicationsStatsExporter extends Exporter
{
    protected static ?string $model = Application::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('UNIVERSITY_NAME')->label('الجامعة'),
            ExportColumn::make('FACULTY_NAME')->label('الكلية'),
            ExportColumn::make('PROGRAM_NAME')->label('التخصص'),
            ExportColumn::make('STUDY_TYPE_NAME')->label('النظام الدراسي'),
            ExportColumn::make('total_count')->label('الطلبات'),
            ExportColumn::make('paid_count')->label('المسددة'),
            ExportColumn::make('accepted_count')->label('المقبولة'),
            ExportColumn::make('confirmed_count')->label('المؤكدة'),
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
