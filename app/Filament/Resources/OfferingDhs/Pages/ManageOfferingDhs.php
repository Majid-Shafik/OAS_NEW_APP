<?php

namespace App\Filament\Resources\OfferingDhs\Pages;

use App\Filament\Resources\OfferingDhs\OfferingDhResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOfferingDhs extends ManageRecords
{
    protected static string $resource = OfferingDhResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
