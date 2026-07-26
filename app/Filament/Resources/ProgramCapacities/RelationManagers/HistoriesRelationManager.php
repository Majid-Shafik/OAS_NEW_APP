<?php

namespace App\Filament\Resources\ProgramCapacities\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'سجل التعديلات';
    
    protected static ?string $modelLabel = 'سجل';

    protected static ?string $pluralModelLabel = 'سجلات التعديلات';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('OLD_ENROLLMENT_CAPACITY')
                    ->label('الطاقة القديمة')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('NEW_ENROLLMENT_CAPACITY')
                    ->label('الطاقة الجديدة')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('NOTES')
                    ->label('ملاحظات')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('PC_IDENT')
            ->columns([
                Tables\Columns\TextColumn::make('OLD_ENROLLMENT_CAPACITY')
                    ->label('الطاقة القديمة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('NEW_ENROLLMENT_CAPACITY')
                    ->label('الطاقة الجديدة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.USER_NAME')
                    ->label('بواسطة')
                    ->sortable(),
                Tables\Columns\TextColumn::make('UPDATED_ON')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('NOTES')
                    ->label('ملاحظات')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                    CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                 EditAction::make(),
                 DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
