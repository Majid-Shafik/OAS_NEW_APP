<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateClearingApplicant extends CreateRecord
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['IS_CLEARING'] = \App\Enums\IsClearingType::CLEARING->value;
        return parent::mutateFormDataBeforeCreate($data);
    }
}
