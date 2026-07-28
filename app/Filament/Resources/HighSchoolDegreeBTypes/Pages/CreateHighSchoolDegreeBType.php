<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Pages;

use App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateHighSchoolDegreeBType extends CreateRecord
{
    protected static string $resource = HighSchoolDegreeBTypeResource::class;

    public function mount(): void
    {
        parent::mount();

        $dataToFill = [];
        if (request()->has('SEC_SCHOOL_SEATNO')) {
            $dataToFill['SEC_SCHOOL_SEATNO'] = request()->query('SEC_SCHOOL_SEATNO');
        }
        if (request()->has('SEC_SCHOOL_YEAR')) {
            $dataToFill['SEC_SCHOOL_YEAR'] = request()->query('SEC_SCHOOL_YEAR');
        }
        if (request()->has('UNID')) {
            $dataToFill['UNID'] = request()->query('UNID');
        }

        if (!empty($dataToFill)) {
            $this->form->fill($dataToFill);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['SEC_SCHOOL_CERTIFICATE'])) {
            $path = $data['SEC_SCHOOL_CERTIFICATE'];
            $data['SEC_SCHOOL_CERTIFICATE'] = basename($path, '.jpg');
        }
        
        $data['RECORDDATE'] = now();
        $data['INSERTED_BY'] = auth()->id();
        
        return $data;
    }
}
