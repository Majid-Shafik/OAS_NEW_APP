<?php

namespace App\Filament\Resources\DeletedApplications\Pages;

use App\Filament\Resources\DeletedApplications\DeletedApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListDeletedApplications extends ListRecords
{
    protected static string $resource = DeletedApplicationResource::class;
}
