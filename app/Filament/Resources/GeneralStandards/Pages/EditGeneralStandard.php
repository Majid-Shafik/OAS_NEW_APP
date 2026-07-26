<?php

namespace App\Filament\Resources\GeneralStandards\Pages;

use App\Filament\Resources\GeneralStandards\GeneralStandardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGeneralStandard extends EditRecord
{
    protected static string $resource = GeneralStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
