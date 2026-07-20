<?php

namespace App\Filament\Resources\Offerings;

use App\Filament\Resources\Offerings\Pages\ManageOfferings;
use App\Models\Offering;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
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
                TextInput::make('UNID')
                    ->required()
                    ->numeric(),
                TextInput::make('OFFER_GROUP_IDENT')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('FACULTY_IDENT')
                    ->required()
                    ->numeric(),
                TextInput::make('PROGRAM_IDENT')
                    ->required()
                    ->numeric(),
                TextInput::make('STUDYTYPE_IDENT')
                    ->required()
                    ->numeric(),
                TextInput::make('SEC_SCHOOL_TYPE')
                    ->required(),
                TextInput::make('SEC_SCHOOL_ACCEPT_RATE')
                    ->required()
                    ->numeric(),
                TextInput::make('ENTRANCE_EXAM_WEIGHT')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('Y_SEC_SCHOOL_MAX_AGE')
                    ->required()
                    ->numeric(),
                TextInput::make('NY_SEC_SCHOOL_MAX_AGE')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('STUDY_FEES')
                    ->default('1000'),
                TextInput::make('STUDY_FEES_NY'),
                Toggle::make('ENTRANCE_EXAM_REQUIRED'),
                DatePicker::make('FROM_DATE')
                    ->required(),
                DatePicker::make('TO_DATE')
                    ->required(),
                Toggle::make('SHOW_ALL_APPLICANTS'),
                Toggle::make('DIRCT_RIGESTER')
                    ->required(),
                DateTimePicker::make('RECORD_ON')
                    ->required(),
                TextInput::make('RECORD_BY')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('LAST_UPDATED_ON')
                    ->required(),
                TextInput::make('LAST_UPDATED_BY')
                    ->required()
                    ->numeric(),
                Toggle::make('APPROVAL'),
                TextInput::make('APPROVAL_BY')
                    ->numeric(),
                Section::make('البيانات الأساسية')
                    ->schema([
                        \Filament\Forms\Components\Select::make('UNID')
                            ->label('الجامعة')
                            ->relationship('university', 'U_NAME')
                            ->live()
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('FACULTY_IDENT')
                            ->label('الكلية')
                            ->relationship('faculty', 'FACULTY_NAME', fn (\Illuminate\Database\Eloquent\Builder $query, \Filament\Schemas\Components\Utilities\Get $get) => $query->where('UNID', $get('UNID')))
                            ->live()
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('PROGRAM_IDENT')
                            ->label('التخصص')
                            ->relationship('program', 'PROGRAM_NAME', fn (\Illuminate\Database\Eloquent\Builder $query, \Filament\Schemas\Components\Utilities\Get $get) => $query->where('UNID', $get('UNID'))->where('FACULTY_IDENT', $get('FACULTY_IDENT')))
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('STUDYTYPE_IDENT')
                            ->label('النوع الدراسي')
                            ->relationship('studyType', 'STUDYTYPE_NAME', fn (\Illuminate\Database\Eloquent\Builder $query, \Filament\Schemas\Components\Utilities\Get $get) => clone $get('UNID') ? $query->where('UNID', clone $get('UNID')) : $query)
                            ->searchable()
                            ->required(),
                        \Filament\Forms\Components\Select::make('SEC_SCHOOL_TYPE')
                            ->label('نوع الثانوية')
                            ->options([
                                'علمي' => 'علمي',
                                'أدبي' => 'أدبي',
                                'تجاري' => 'تجاري',
                                'صناعي' => 'صناعي',
                                'أخرى' => 'أخرى',
                            ])
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('تفضيلات القبول')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('SEC_SCHOOL_ACCEPT_RATE')
                            ->label('معدل القبول')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('ENTRANCE_EXAM_WEIGHT')
                            ->label('وزن امتحان القبول')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('Y_SEC_SCHOOL_MAX_AGE')
                            ->label('أقصى عمر لثانوية اليمن')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('NY_SEC_SCHOOL_MAX_AGE')
                            ->label('أقصى عمر لثانوية غير اليمن')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('STUDY_FEES')
                            ->label('الرسوم الدراسية')
                            ->numeric(),
                        \Filament\Forms\Components\TextInput::make('STUDY_FEES_NY')
                            ->label('الرسوم الدراسية (غير يمني)')
                            ->numeric(),
                        \Filament\Forms\Components\Toggle::make('ENTRANCE_EXAM_REQUIRED')
                            ->label('امتحان القبول مطلوب؟'),
                        \Filament\Forms\Components\DatePicker::make('FROM_DATE')
                            ->label('من تاريخ'),
                        \Filament\Forms\Components\DatePicker::make('TO_DATE')
                            ->label('إلى تاريخ'),
                        \Filament\Forms\Components\Toggle::make('SHOW_ALL_APPLICANTS')
                            ->label('إظهار كل المتقدمين'),
                        \Filament\Forms\Components\Toggle::make('DIRCT_RIGESTER')
                            ->label('تسجيل مباشر'),
                    ])->columns(2),
            ]);
    }


    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المعيار الأساسية')
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة'),
                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                        TextEntry::make('studyType.STUDYTYPE_NAME')->label('النوع الدراسي'),
                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية'),
                    ])->columns(2),
                Section::make('تفضيلات وإعدادات القبول')
                    ->schema([
                        TextEntry::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول'),
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
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
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
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_ACCEPT_RATE')
                    ->label('معدل القبول')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ENTRANCE_EXAM_WEIGHT')
                    ->label('وزن امتحان القبول')
                    ->numeric()
                    ->sortable(),

                ColumnGroup::make("فترة التنسيق", [ 
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
            ])
            ->filters([
                \App\Filament\Filters\AcademicFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOfferings::route('/'),
        ];
    }
}
