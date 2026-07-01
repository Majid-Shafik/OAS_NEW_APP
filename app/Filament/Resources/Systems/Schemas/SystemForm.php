<?php

namespace App\Filament\Resources\Systems\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SystemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('System Details')
                    ->description('Main information about the enterprise system.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            
                            TextInput::make('english_name'),
                            
                            TextInput::make('slug')
                                ->required()
                                ->unique(ignoreRecord: true),
                                
                            TextInput::make('url')
                                ->label('System URL')
                                ->url()
                                ->required(),
                        ]),
                        
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('Branding & Display')
                    ->description('Customize how this system card appears on the portal.')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('thumbnail')
                                ->image()
                                ->imageEditor()
                                ->directory('system-thumbnails')
                                ->columnSpanFull(),
                                
                            TextInput::make('icon')
                                ->label('Heroicon Name')
                                ->placeholder('heroicon-o-academic-cap')
                                ->helperText('Find icons at heroicons.com (use the outer class name)'),
                                
                            ColorPicker::make('color')
                                ->label('Theme Color')
                                ->default('#4f46e5'),
                                
                            TextInput::make('display_order')
                                ->required()
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),
                    
                Section::make('Settings')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->helperText('If disabled, it will not appear on the public portal.')
                                ->default(true)
                                ->required(),
                                
                            Toggle::make('open_in_new_tab')
                                ->label('Open in New Tab')
                                ->default(false)
                                ->required(),
                        ]),
                    ]),
            ]);
    }
}
