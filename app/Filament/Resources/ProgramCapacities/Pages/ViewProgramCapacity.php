<?php

namespace App\Filament\Resources\ProgramCapacities\Pages;

use App\Filament\Resources\ProgramCapacities\ProgramCapacityResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramCapacity extends ViewRecord
{
    protected static string $resource = ProgramCapacityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
