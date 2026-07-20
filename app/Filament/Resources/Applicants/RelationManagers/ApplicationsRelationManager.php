<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'طلبات التقديم';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('APPLICATION_IDENT')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('APPLICATION_IDENT')
            ->columns([
                Tables\Columns\TextColumn::make('university.U_NAME')
                    ->label(__('University')),
                Tables\Columns\TextColumn::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT')),
                Tables\Columns\TextColumn::make('faculty.FACULTY_NAME')
                    ->label(__('Faculty')),
                Tables\Columns\TextColumn::make('program.PROGRAM_NAME')
                    ->label(__('Program')),
                Tables\Columns\TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label(__('STUDYTYPE_IDENT')),
                Tables\Columns\TextColumn::make('CHOICE_NO')
                    ->label(__('CHOICE_NO')),
                Tables\Columns\TextColumn::make('paymentMethod.PAY_METHOD')
                    ->label(__('PAYMENT_FLAG')),
                Tables\Columns\TextColumn::make('STATUS')
                    ->label(__('STATUS'))
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Read-only
            ])
            ->recordActions([
                // Read-only
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }
}
