<?php

namespace App\Filament\Resources\Offerings\RelationManagers;

use App\Filament\Resources\OfferingDhs\OfferingDhResource;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

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
            ->columns(OfferingDhResource::getSharedTableColumns(true))
            ->filters([
                //
            ])
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
