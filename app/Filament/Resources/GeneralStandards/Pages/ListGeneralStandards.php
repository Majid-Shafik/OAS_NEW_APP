<?php

namespace App\Filament\Resources\GeneralStandards\Pages;

use App\Filament\Resources\GeneralStandards\GeneralStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeneralStandards extends ListRecords
{
    protected static string $resource = GeneralStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
