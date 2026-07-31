<?php

namespace App\Filament\Resources\HighSchoolDegreeHistories\Pages;

use App\Filament\Resources\HighSchoolDegreeHistories\HighSchoolDegreeHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHighSchoolDegreeHistories extends ListRecords
{
    protected static string $resource = HighSchoolDegreeHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
