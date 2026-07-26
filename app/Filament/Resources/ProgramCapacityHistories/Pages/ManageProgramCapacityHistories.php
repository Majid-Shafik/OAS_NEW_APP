<?php

namespace App\Filament\Resources\ProgramCapacityHistories\Pages;

use App\Filament\Resources\ProgramCapacityHistories\ProgramCapacityHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProgramCapacityHistories extends ManageRecords
{
    protected static string $resource = ProgramCapacityHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
