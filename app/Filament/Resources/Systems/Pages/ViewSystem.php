<?php

namespace App\Filament\Resources\Systems\Pages;

use App\Filament\Resources\Systems\SystemResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSystem extends ViewRecord
{
    protected static string $resource = SystemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
