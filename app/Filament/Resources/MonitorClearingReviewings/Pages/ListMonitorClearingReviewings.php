<?php

namespace App\Filament\Resources\MonitorClearingReviewings\Pages;

use App\Filament\Resources\MonitorClearingReviewings\MonitorClearingReviewingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitorClearingReviewings extends ListRecords
{
    protected static string $resource = MonitorClearingReviewingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action because this is an audit log
        ];
    }
}
