<?php

namespace App\Filament\Resources\Offerings\Schemas;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\StudyType;
use App\Models\University;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OfferingForm
{
    public static function configure(Schema $schema): Schema
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
                            ->options(\App\Models\ComboValue::getOptionsValuesByCode(1))
                            ->searchable()
                            ->required()
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, Get $get) {
                                    return $rule
                                        ->where('UNID', $get('UNID'))
                                        ->where('FACULTY_IDENT', $get('FACULTY_IDENT'))
                                        ->where('PROGRAM_IDENT', $get('PROGRAM_IDENT'))
                                        ->where('STUDYTYPE_IDENT', $get('STUDYTYPE_IDENT'));
                                }
                            )
                            ->validationMessages([
                                'unique' => 'يوجد معيار مسبق مسجل بنفس هذه البيانات (الجامعة، الكلية، التخصص، النظام الدراسي، ونوع الثانوية). يرجى التعديل عليه بدلاً من إضافة واحد جديد.',
                            ]),
                    ])->columns(3),

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
                            ->label('من تاريخ التنسيق')
                            ->translateLabel()
                            ->required(),
                        DatePicker::make('TO_DATE')
                            ->label('إلى تاريخ التنسيق')
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

                    ])->columns(4),

                Section::make('إعدادات مجموعة التنسيق')
                    ->relationship('offeringGroup')
                    ->hiddenOn('create')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('DESCRIPTION')->label('وصف المجموعة')->required()->columnSpan(2),
                        TextInput::make('MIN_CHOICE')->label('الحد الأدنى للرغبات')->numeric()->required(),
                        TextInput::make('MAX_CHOICE')->label('الحد الأعلى للرغبات')->numeric()->required(),
                        TextInput::make('APPLYING_COST')->label('رسوم التنسيق')->numeric()->required(),
                        Toggle::make('ENABLE_PAYMENT')->label('تفعيل الدفع')->live(),
                        DatePicker::make('STARTED_PAYMENT_DATE')
                            ->label('تاريخ بداية السداد')
                            ->visible(fn(Get $get) => $get('ENABLE_PAYMENT'))
                            ->required(fn(Get $get) => $get('ENABLE_PAYMENT'))
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $fromDate = $get('../FROM_DATE');
                                    if ($fromDate && $value) {
                                        $valueDate = date('Y-m-d', strtotime($value));
                                        $fromDateOnly = date('Y-m-d', strtotime($fromDate));
                                        if ($valueDate < $fromDateOnly) {
                                            $fail('تاريخ بداية السداد يجب أن يكون مساوياً أو بعد تاريخ بداية فترة التنسيق.');
                                        }
                                    }
                                };
                            }),
                        DateTimePicker::make('FINISHED_PAYMENT_DATE')
                            ->label('تاريخ نهاية السداد')
                            ->visible(fn(Get $get) => $get('ENABLE_PAYMENT'))
                            ->required(fn(Get $get) => $get('ENABLE_PAYMENT'))
                            ->rule(function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $started = $get('STARTED_PAYMENT_DATE');
                                    if ($started && $value && strtotime($value) < strtotime($started)) {
                                        $fail('تاريخ نهاية السداد يجب أن يكون مساوياً أو بعد تاريخ بداية السداد.');
                                    }
                                    $toDate = $get('../TO_DATE');
                                    if ($toDate && $value) {
                                        $valueDate = date('Y-m-d', strtotime($value));
                                        $toDateOnly = date('Y-m-d', strtotime($toDate));
                                        if ($valueDate < $toDateOnly) {
                                            $fail('تاريخ نهاية السداد يجب أن يكون مساوياً أو بعد تاريخ نهاية فترة التنسيق.');
                                        }
                                    }
                                };
                            }),
                    ])->columns(4),

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
}
