<?php

namespace App\Filament\Resources\Offerings\OfferingResource\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfferingDhsRelationManager extends RelationManager
{
    protected static string $relationship = 'offeringDhs';

    protected static ?string $title = 'تعديلات المعيار';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('OFFERING_IDENT')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns(\App\Filament\Resources\OfferingDhs\OfferingDhResource::getSharedTableColumns(true))
            ->filters(\App\Filament\Resources\OfferingDhs\OfferingDhResource::getSharedTableFilters())
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
