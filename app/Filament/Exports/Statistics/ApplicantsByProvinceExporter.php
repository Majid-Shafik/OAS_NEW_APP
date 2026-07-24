<?php

namespace App\Filament\Exports\Statistics;

use App\Models\Applicant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ApplicantsByProvinceExporter extends Exporter
{
    protected static ?string $model = Applicant::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('PROVINCE_NAME')->label('المحافظة'),
            ExportColumn::make('total_count')->label('إجمالي المتقدمين'),
            ExportColumn::make('updated_count')->label('مُحدّث (UPDATED)'),
            ExportColumn::make('ready_count')->label('جاهز (READY)'),
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
