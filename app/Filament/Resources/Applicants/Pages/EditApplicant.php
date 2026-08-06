<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use App\Filament\Traits\HandlesApplicantAttachmentUploads;
use App\Filament\Traits\HasMinistryRefreshAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditApplicant extends EditRecord
{
    use HasMinistryRefreshAction;
    use HandlesApplicantAttachmentUploads;

    protected static string $resource = ApplicantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['COUNTRY_IDENT']) && !empty($data['COUNTRY_NAME'])) {
            $data['COUNTRY_IDENT'] = \App\Models\Country::where('COUNTRY_NAME', $data['COUNTRY_NAME'])->value('COUNTRY_IDENT');
            if (empty($data['COUNTRY_IDENT']) && ($data['YEMEN_NATIONAL'] ?? 0) == 1) {
                $data['COUNTRY_IDENT'] = 242;
            }
        }

        return $this->mutateApplicantAttachmentData($data);
    }

    protected function afterSave(): void
    {
        $this->syncApplicantAttachments();
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
