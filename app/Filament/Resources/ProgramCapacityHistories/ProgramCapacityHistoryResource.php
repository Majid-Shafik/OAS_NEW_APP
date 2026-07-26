<?php

namespace App\Filament\Resources\ProgramCapacityHistories;

use App\Filament\Resources\ProgramCapacityHistories\Pages\ManageProgramCapacityHistories;
use App\Filament\Resources\ProgramCapacityHistories\Pages\ViewProgramCapacityHistory;
use App\Models\Program;
use App\Models\ProgramCapacityHistory;
use App\Models\StudyType;
use App\Models\University;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ProgramCapacityHistoryResource extends Resource
{
    protected static ?string $model = ProgramCapacityHistory::class;

    protected static UnitEnum|string|null $navigationGroup = 'المعايير';

    protected static ?string $modelLabel = 'سجل الطاقة الاستيعابية';

    protected static ?string $pluralModelLabel = 'سجلات الطاقات الاستيعابية';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات السجل')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('UNID')
                            ->label('الجامعة')
                            ->options(University::pluck('U_NAME', 'UNID'))
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('PROGRAM_IDENT')
                            ->label('التخصص')
                            ->options(fn (Get $get) => Program::where('UNID', $get('UNID'))->pluck('PROGRAM_NAME', 'PROGRAM_IDENT'))
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('STUDYTYPE_IDENT')
                            ->label('النظام الدراسي')
                            ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                            ->live()
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                Section::make('تفاصيل التعديل')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('OLD_ENROLLMENT_CAPACITY')
                            ->label('الطاقة الاستيعابية القديمة')
                            ->numeric()
                            ->required(),
                        TextInput::make('NEW_ENROLLMENT_CAPACITY')
                            ->label('الطاقة الاستيعابية الجديدة')
                            ->numeric()
                            ->required(),
                        Textarea::make('NOTES')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات السجل')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                        TextEntry::make('studyType.STUDYTYPE_NAME')->label('النظام الدراسي'),
                    ])->columns(3),

                Section::make('تفاصيل التعديل')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('OLD_ENROLLMENT_CAPACITY')->label('الطاقة القديمة'),
                        TextEntry::make('NEW_ENROLLMENT_CAPACITY')->label('الطاقة الجديدة'),
                        TextEntry::make('user.USER_NAME')->label('بواسطة'),
                        TextEntry::make('UPDATED_ON')->label('تاريخ التحديث')->dateTime(),
                        TextEntry::make('NOTES')->label('ملاحظات')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('OLD_ENROLLMENT_CAPACITY')
                    ->label('الطاقة القديمة')
                    ->sortable(),
                TextColumn::make('NEW_ENROLLMENT_CAPACITY')
                    ->label('الطاقة الجديدة')
                    ->sortable(),
                TextColumn::make('user.USER_NAME')
                    ->label('بواسطة')
                    ->sortable(),
                TextColumn::make('UPDATED_ON')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProgramCapacityHistories::route('/'),
            'view' => ViewProgramCapacityHistory::route('/{record}'),
        ];
    }
}
