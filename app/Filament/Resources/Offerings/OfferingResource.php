<?php

namespace App\Filament\Resources\Offerings;

use App\Filament\Filters\AcademicFilter;
use App\Filament\Resources\Offerings\OfferingResource\RelationManagers\OfferingDhsRelationManager;
use App\Filament\Resources\Offerings\OfferingResource\RelationManagers\RequestAdjustOfferingsRelationManager;
use App\Filament\Resources\Offerings\Pages\ManageOfferings;
use App\Filament\Resources\Offerings\Pages\ViewOffering;
use App\Models\Faculty;
use App\Models\Offering;
use App\Models\Program;
use App\Models\StudyType;
use App\Models\University;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class OfferingResource extends Resource
{
    protected static ?string $model = Offering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'المعايير';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'معيار';

    protected static ?string $pluralModelLabel = 'المعايير';

    protected static ?string $recordTitleAttribute = 'OFFERING_IDENT';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('UNID')
                            ->translateLabel()
                            ->options(University::pluck('U_NAME', 'UNID'))
                            ->default(fn () => auth()->user()->UNID > 0 ? auth()->user()->UNID : null)
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('FACULTY_IDENT')
                            ->translateLabel()
                            ->options(fn (Get $get) => Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT'))
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('PROGRAM_IDENT')
                            ->translateLabel()
                            ->options(fn (Get $get) => Program::where('UNID', $get('UNID'))->where('FACULTY_IDENT', $get('FACULTY_IDENT'))->pluck('PROGRAM_NAME', 'PROGRAM_IDENT'))
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('STUDYTYPE_IDENT')
                            ->translateLabel()
                            ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                            ->live()
                            ->searchable()
                            ->required(),
                        Select::make('SEC_SCHOOL_TYPE')
                            ->label('نوع الثانوية')
                            ->options(\App\Models\ComboValue::getOptionsByCode(1))
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Section::make('تفضيلات القبول')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('SEC_SCHOOL_ACCEPT_RATE')
                            ->label('معدل القبول')
                            ->numeric()
                            ->suffix('%')
                            ->required(),
                        TextInput::make('ENTRANCE_EXAM_WEIGHT')
                            ->translateLabel()
                            ->required()
                            ->numeric()
                            ->default(0.0),
                        TextInput::make('Y_SEC_SCHOOL_MAX_AGE')
                            ->translateLabel()
                            ->required()
                            ->numeric(),
                        TextInput::make('NY_SEC_SCHOOL_MAX_AGE')
                            ->translateLabel()
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('STUDY_FEES')
                            ->translateLabel()
                            ->default('1000'),
                        TextInput::make('STUDY_FEES_NY')
                            ->translateLabel(),

                        DatePicker::make('FROM_DATE')
                            ->translateLabel()
                            ->required(),
                        DatePicker::make('TO_DATE')
                            ->translateLabel()
                            ->required(),
                        Grid::make(3)->schema([
                            Toggle::make('ENTRANCE_EXAM_REQUIRED')
                                ->translateLabel(),
                            Toggle::make('SHOW_ALL_APPLICANTS')
                                ->translateLabel(),
                            Toggle::make('DIRCT_RIGESTER')
                                ->translateLabel()
                                ->required(),

                        ])->columnSpanFull(),

                    ])->columns(2),

                Section::make('إعدادات مجموعة التنسيق')
                    ->relationship('offeringGroup')
                    ->hiddenOn('create')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('DESCRIPTION')->label('وصف المجموعة')->required(),
                        TextInput::make('MIN_CHOICE')->label('الحد الأدنى للرغبات')->numeric()->required(),
                        TextInput::make('MAX_CHOICE')->label('الحد الأعلى للرغبات')->numeric()->required(),
                        TextInput::make('APPLYING_COST')->label('رسوم التنسيق')->numeric()->required(),
                        Toggle::make('ENABLE_PAYMENT')->label('تفعيل الدفع'),
                    ])->columns(2),

                Section::make('التدقيق والمراجعة')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('RECORD_ON')
                            ->translateLabel()
                            ->required(),
                        TextInput::make('RECORD_BY')
                            ->translateLabel()
                            ->required()
                            ->numeric(),
                        DateTimePicker::make('LAST_UPDATED_ON')
                            ->translateLabel()
                            ->required(),
                        TextInput::make('LAST_UPDATED_BY')
                            ->translateLabel()
                            ->required()
                            ->numeric(),
                        Toggle::make('APPROVAL'),
                        TextInput::make('APPROVAL_BY')
                            ->numeric(),
                    ])->columns(2)
                    ->hiddenOn(['create', 'edit']),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المعيار الأساسية')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة'),
                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                        TextEntry::make('studyType.STUDYTYPE_NAME')->label('النوع الدراسي'),
                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                            ->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(1, $state)),
                    ])->columns(2),
                Section::make('معلومات مجموعة التنسيق')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('offeringGroup.DESCRIPTION')->label('وصف المجموعة'),
                        TextEntry::make('offeringGroup.MIN_CHOICE')->label('الحد الأدنى للرغبات'),
                        TextEntry::make('offeringGroup.MAX_CHOICE')->label('الحد الأعلى للرغبات'),
                        TextEntry::make('offeringGroup.APPLYING_COST')->label('رسوم التنسيق'),
                        IconEntry::make('offeringGroup.ENABLE_PAYMENT')->label('تفعيل الدفع')->boolean(),
                    ])->columns(3),
                Section::make('تفضيلات وإعدادات القبول')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول')->suffix('%'),
                        TextEntry::make('ENTRANCE_EXAM_WEIGHT')->label('وزن امتحان القبول'),
                        TextEntry::make('Y_SEC_SCHOOL_MAX_AGE')->label('أقصى عمر يمني'),
                        TextEntry::make('NY_SEC_SCHOOL_MAX_AGE')->label('أقصى عمر غير يمني'),
                        TextEntry::make('STUDY_FEES')->label('الرسوم الدراسية'),
                        TextEntry::make('STUDY_FEES_NY')->label('الرسوم لغير اليمني'),
                        IconEntry::make('ENTRANCE_EXAM_REQUIRED')->label('امتحان القبول مطلوب؟')->boolean(),
                        TextEntry::make('FROM_DATE')->label('من تاريخ')->date(),
                        TextEntry::make('TO_DATE')->label('إلى تاريخ')->date(),
                    ])->columns(3),
                Section::make('معلومات التسجيل والمراجعة')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('recordedBy.USER_NAME')->label('بواسطة (إضافة)'),
                        TextEntry::make('RECORD_ON')->label('تاريخ الإضافة')->dateTime(),
                        TextEntry::make('lastUpdatedBy.USER_NAME')->label('بواسطة (تحديث)'),
                        TextEntry::make('LAST_UPDATED_ON')->label('تاريخ التحديث')->dateTime(),
                        TextEntry::make('approvalBy.USER_NAME')->label('تم الاعتماد بواسطة'),
                        TextEntry::make('APPROVAL_ON')->label('تاريخ الاعتماد')->dateTime(),
                        IconEntry::make('APPROVAL')->label('حالة الاعتماد')->boolean(),
                        TextEntry::make('APPROVAL_REGECT_REASON')->label('سبب الرفض'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('OFFERING_IDENT')
                    ->label('الرقم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النوع الدراسي')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_TYPE')
                    ->label('نوع الثانوية')
                    ->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(1, $state))
                    ->searchable(),
                TextColumn::make('offeringGroup.DESCRIPTION')
                    ->label('مجموعة التنسيق')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_ACCEPT_RATE')
                    ->label('معدل القبول')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('ENTRANCE_EXAM_WEIGHT')
                    ->label('وزن امتحان القبول')
                    ->numeric()
                    ->sortable(),

                ColumnGroup::make('فترة التنسيق', [
                    TextColumn::make('FROM_DATE')
                        ->label('من تاريخ')
                        ->date()
                        ->sortable(),
                    TextColumn::make('TO_DATE')
                        ->label('إلى تاريخ')
                        ->date()
                        ->sortable(),
                ]),
                ColumnGroup::make('عمر الثانوية', [
                    TextColumn::make('Y_SEC_SCHOOL_MAX_AGE')
                        ->label('عمر الثانوي (يمني)')
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('NY_SEC_SCHOOL_MAX_AGE')
                        ->label('عمر الثانوي (غير يمني)')
                        ->numeric()
                        ->sortable(),
                ]),
                IconColumn::make('ENTRANCE_EXAM_REQUIRED')
                    ->label('امتحان مطلوب')
                    ->boolean(),
                ColumnGroup::make('معلومات التسجيل والمراجعة', [
                    TextColumn::make('lastUpdatedBy.USER_NAME')
                        ->label('تم التعديل بواسطة')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('LAST_UPDATED_ON')
                        ->label('تاريخ التحديث')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('approvalBy.USER_NAME')
                        ->label('الاعتماد بواسطة')
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('APPROVAL_ON')
                        ->label('تاريخ الاعتماد')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    IconColumn::make('APPROVAL')
                        ->label('حالة الاعتماد')
                        ->boolean()
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                AcademicFilter::make(),
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
        return [
            OfferingDhsRelationManager::class,
            RequestAdjustOfferingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOfferings::route('/'),
            'view' => ViewOffering::route('/{record}'),
        ];
    }
}
