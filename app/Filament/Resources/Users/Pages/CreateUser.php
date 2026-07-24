<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

     protected function getRedirectUrl(): string
    {
        // العودة إلى صفحة القائمة (Index)
        return $this->getResource()::getUrl('index');
    }
}
