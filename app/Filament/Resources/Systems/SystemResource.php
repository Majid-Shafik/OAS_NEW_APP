<?php

namespace App\Filament\Resources\Systems;

use App\Filament\Resources\Systems\Pages\CreateSystem;
use App\Filament\Resources\Systems\Pages\EditSystem;
use App\Filament\Resources\Systems\Pages\ListSystems;
use App\Filament\Resources\Systems\Pages\ViewSystem;
use App\Filament\Resources\Systems\Schemas\SystemForm;
use App\Filament\Resources\Systems\Schemas\SystemInfolist;
use App\Filament\Resources\Systems\Tables\SystemsTable;
use App\Models\System;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SystemResource extends Resource
{
    protected static ?string $model = System::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SystemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SystemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SystemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSystems::route('/'),
            'create' => CreateSystem::route('/create'),
            'view' => ViewSystem::route('/{record}'),
            'edit' => EditSystem::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
