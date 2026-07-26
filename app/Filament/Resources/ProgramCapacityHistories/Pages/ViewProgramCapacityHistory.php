<?php

namespace App\Filament\Resources\ProgramCapacityHistories\Pages;

use App\Filament\Resources\ProgramCapacityHistories\ProgramCapacityHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramCapacityHistory extends ViewRecord
{
    protected static string $resource = ProgramCapacityHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
