<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Pages;

use App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource;
use App\Filament\Resources\HighSchoolDegreeBTypes\Widgets\HighSchoolDegreeBTypeStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHighSchoolDegreeBTypes extends ListRecords
{
    protected static string $resource = HighSchoolDegreeBTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HighSchoolDegreeBTypeStats::class,
        ];
    }
}
