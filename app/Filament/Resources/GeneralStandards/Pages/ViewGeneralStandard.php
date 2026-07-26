<?php

namespace App\Filament\Resources\GeneralStandards\Pages;

use App\Filament\Resources\GeneralStandards\GeneralStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGeneralStandard extends ViewRecord
{
    protected static string $resource = GeneralStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
