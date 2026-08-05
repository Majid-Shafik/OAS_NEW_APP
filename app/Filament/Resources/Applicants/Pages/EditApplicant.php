<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use App\Filament\Traits\HasMinistryRefreshAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditApplicant extends EditRecord
{
    use HasMinistryRefreshAction;

    protected static string $resource = ApplicantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['COUNTRY_IDENT']) && !empty($data['COUNTRY_NAME'])) {
            $data['COUNTRY_IDENT'] = \App\Models\Country::where('COUNTRY_NAME', $data['COUNTRY_NAME'])->value('COUNTRY_IDENT');
            if (empty($data['COUNTRY_IDENT']) && ($data['YEMEN_NATIONAL'] ?? 0) == 1) {
                $data['COUNTRY_IDENT'] = 242;
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getApiRefreshAction(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
