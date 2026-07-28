<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Pages;

use App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHighSchoolDegreeBType extends EditRecord
{
    protected static string $resource = HighSchoolDegreeBTypeResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['SEC_SCHOOL_CERTIFICATE'])) {
            $portalYear = \App\Helpers\PortalHelper::getActiveYear();
            $basename = $data['SEC_SCHOOL_CERTIFICATE'];
            $data['SEC_SCHOOL_CERTIFICATE'] = "uploads/p{$portalYear}/images/attachments/secondary/{$basename}.jpg";
        }
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['SEC_SCHOOL_CERTIFICATE'])) {
            $path = $data['SEC_SCHOOL_CERTIFICATE'];
            $data['SEC_SCHOOL_CERTIFICATE'] = basename($path, '.jpg');
        }
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
