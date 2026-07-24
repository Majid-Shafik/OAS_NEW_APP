<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'طلبات التقديم';

    public function form(Schema $schema): Schema
    {
        return ApplicationResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ApplicationResource::table($table);
    }

    public function infolist(Schema $schema): Schema
    {
        return ApplicationResource::infolist($schema);
    }
}

