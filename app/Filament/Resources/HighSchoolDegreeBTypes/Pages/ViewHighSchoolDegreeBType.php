<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Pages;

use App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHighSchoolDegreeBType extends ViewRecord
{
    protected static string $resource = HighSchoolDegreeBTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            HighSchoolDegreeBTypeResource::getReviewAction(\Filament\Actions\Action::class),
            Actions\EditAction::make(),
        ];
    }
}
