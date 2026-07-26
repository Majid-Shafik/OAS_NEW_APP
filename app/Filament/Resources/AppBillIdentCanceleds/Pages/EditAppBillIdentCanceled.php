<?php

namespace App\Filament\Resources\AppBillIdentCanceleds\Pages;

use App\Filament\Resources\AppBillIdentCanceleds\AppBillIdentCanceledResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppBillIdentCanceled extends EditRecord
{
    protected static string $resource = AppBillIdentCanceledResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
