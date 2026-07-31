<?php

namespace App\Filament\Resources\ClearingApplicants\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ApplicationsClearingRelationManager extends RelationManager
{
    protected static string $relationship = 'applicationsClearing';

    protected static ?string $title = 'الجامعة السابقة والتخصص';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('FROM_UNIV_NAME')
                    ->label('اسم الجامعة السابقة')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('FROM_FACULTY_NAME')
                    ->label('اسم الكلية السابقة')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('FROM_PROGRAM_NAME')
                    ->label('اسم التخصص السابق')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('NO_STUDY_YEARS')
                    ->label('عدد سنوات الدراسة')
                    ->numeric(),
                Forms\Components\TextInput::make('STUDY_LEVEL')
                    ->label('مستوى الدراسة')
                    ->maxLength(255),
                Forms\Components\TextInput::make('FROM_YEAR')
                    ->label('سنة الالتحاق')
                    ->maxLength(255),
                Forms\Components\TextInput::make('MOVING_REASON')
                    ->label('سبب الانتقال')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('FROM_UNIV_NAME')
            ->columns([
                Tables\Columns\TextColumn::make('FROM_UNIV_NAME')->label('الجامعة'),
                Tables\Columns\TextColumn::make('FROM_FACULTY_NAME')->label('الكلية'),
                Tables\Columns\TextColumn::make('FROM_PROGRAM_NAME')->label('التخصص'),
                Tables\Columns\TextColumn::make('NO_STUDY_YEARS')->label('سنوات الدراسة'),
                Tables\Columns\TextColumn::make('FROM_YEAR')->label('سنة الالتحاق'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }
}
