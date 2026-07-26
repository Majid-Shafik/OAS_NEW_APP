<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Pages;

use App\Filament\Resources\AppBillIdentCanceleds\AppBillIdentCanceledResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAppBillIdentCanceled extends ViewRecord
{
    protected static string $resource = AppBillIdentCanceledResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }
}
