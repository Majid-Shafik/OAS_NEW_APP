<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use App\Filament\Traits\HandlesApplicantAttachmentUploads;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClearingApplicant extends EditRecord
{
    use HandlesApplicantAttachmentUploads;

    protected static string $resource = ClearingApplicantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->mutateApplicantAttachmentData($data);
    }

    protected function afterSave(): void
    {
        $this->syncApplicantAttachments();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
