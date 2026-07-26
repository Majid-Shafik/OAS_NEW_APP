<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClearingApplicants extends ListRecords
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
