<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ApplicantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)->schema([
                    // القسم الأيمن الأكبر (عرض 9)
                    Grid::make(1)->schema([
                        Tabs::make('معلومات المتقدم')
                            ->tabs([
                                Tab::make('بيانات شخصية')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextInput::make('FULL_NAME')
                                            ->label('الاسم الكامل')
                                            ->readOnly()
                                            ->required(),
                                        TextInput::make('NATIONAL_NUMBER')->label('الرقم الوطني')->numeric(),
                                        TextInput::make('FIRST_NAME')
                                            ->label('الاسم الأول')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $set('FULL_NAME', trim($state . ' ' . $get('LAST_NAME')));
                                            }),
                                        TextInput::make('LAST_NAME')
                                            ->label('اللقب')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                $set('FULL_NAME', trim($get('FIRST_NAME') . ' ' . $state));
                                            }),
                                        Select::make('GENDER')->label('الجنس')->options(\App\Enums\Gender::class),
                                        DatePicker::make('DATE_OF_BIRTH')->label('تاريخ الميلاد'),
                                        TextInput::make('PLACE_OF_BIRTH')->label('محل الميلاد'),
                                        Select::make('PROVINCE')->label('المحافظة')
                                            ->options(fn () => \App\Models\Province::pluck('NAME', 'NAME')->filter(fn($v) => !empty($v))->toArray())
                                            ->live()
                                            ->searchable(),
                                        Select::make('TERRITORY')->label('المديرية')
                                            ->options(function (Get $get) {
                                                $province = $get('PROVINCE');
                                                $query = \App\Models\Applicant::distinct()->whereNotNull('TERRITORY');
                                                if ($province) {
                                                    $query->where('PROVINCE', $province);
                                                }
                                                return $query->pluck('TERRITORY', 'TERRITORY')->filter(fn($v) => !empty($v))->toArray();
                                            })
                                            ->searchable(),
                                        Select::make('COUNTRY_NAME')->label('الدولة')
                                            ->options(fn () => \App\Models\Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => !empty($v))->toArray())
                                            ->required()->searchable(),
                                        TextInput::make('IDENT_TYPE')->label('نوع الهوية'),
                                        TextInput::make('IDENT_NO')->label('رقم الهوية'),
                                        Toggle::make('YEMEN_NATIONAL')->label('جنسية يمنية')->required(),
                                        TextInput::make('EMAIL')->label('البريد الإلكتروني'),
                                        TextInput::make('MOBILE_PHONE')->label('رقم الهاتف'),
                                        TextInput::make('BLOOD_GROUP')->label('فصيلة الدم'),
                                    ])->columns(3),

                                Tab::make('بيانات الثانوية')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        TextInput::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')->numeric(),
                                        Select::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                                            ->options(fn () => \App\Models\Applicant::distinct()->whereNotNull('SEC_SCHOOL_TYPE')->pluck('SEC_SCHOOL_TYPE', 'SEC_SCHOOL_TYPE')->filter(fn($v) => !empty($v))->toArray())
                                            ->searchable(),
                                        TextInput::make('SEC_SCHOOL_NAME')->label('اسم المدرسة'),
                                        TextInput::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس'),
                                        TextInput::make('SEC_SCHOOL_RATE')->label('المعدل')->numeric(),
                                        TextInput::make('SEC_SCHOOL_MARK')->label('المجموع')->numeric(),
                                        TextInput::make('SEC_SCHOOL_OVERALLMARK')->label('المجموع الكلي')->numeric(),
                                        Select::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')
                                            ->options(fn () => \App\Models\Province::pluck('NAME', 'NAME')->filter(fn($v) => !empty($v))->toArray())
                                            ->live()
                                            ->searchable(),
                                        Select::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')
                                            ->options(function (Get $get) {
                                                $province = $get('SEC_SCHOOL_PROVINCE');
                                                $query = \App\Models\Applicant::distinct()->whereNotNull('SEC_SCHOOL_TERRITORY');
                                                if ($province) {
                                                    $query->where('SEC_SCHOOL_PROVINCE', $province);
                                                }
                                                return $query->pluck('SEC_SCHOOL_TERRITORY', 'SEC_SCHOOL_TERRITORY')->filter(fn($v) => !empty($v))->toArray();
                                            })
                                            ->required()->searchable(),
                                        TextInput::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية'),
                                    ])->columns(3),

                                Tab::make('بيانات المقاصة والقبول')
                                    ->icon('heroicon-o-document-check')
                                    ->schema([
                                        TextInput::make('APPLICANT_TYPE')->label('نوع المتقدم')->numeric()->default(1)->required(),
                                        DatePicker::make('ADMITTED_ON')->label('تاريخ القبول'),
                                        Select::make('ADMITTED_FACULITY')->label('الكلية المقبول بها')->required()
                                            ->options(fn (Get $get) => Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT'))
                                            ->live()->searchable(),
                                        Select::make('ADMITTED_PROGRAM')->label('التخصص المقبول به')->required()
                                            ->options(fn (Get $get) => Program::where('UNID', $get('UNID'))
                                                ->where('FACULTY_IDENT', $get('ADMITTED_FACULITY'))
                                                ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT'))
                                            ->searchable(),
                                        TextInput::make('ADMITTED_OFFERING')->label('رقم العرض')->numeric(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn ($record, Get $get) => $get('IS_CLEARING') || $record?->IS_CLEARING),

                                Tab::make('بيانات النظام')
                                    ->icon('heroicon-o-server')
                                    ->schema([
                                        DateTimePicker::make('RECORDDATE')->label('تاريخ التسجيل')->required(),
                                        TextInput::make('INSERTED_BY')->label('تم الإدخال بواسطة')->numeric()->default(-1)->required(),
                                        TextInput::make('LAST_UPDATED_BY')->label('آخر تحديث بواسطة')->numeric(),
                                        DateTimePicker::make('LAST_UPDATED_ON')->label('تاريخ آخر تحديث')->required(),
                                        TextInput::make('APPROVED_BY')->label('تم الاعتماد بواسطة')->numeric(),
                                        DateTimePicker::make('APPROVED_ON')->label('تاريخ الاعتماد'),
                                        Toggle::make('IMPORTED')->label('مستورد')->default(false)->required(),
                                        Toggle::make('EXPORTED')->label('مُصدّر')->default(false),
                                        Toggle::make('REVIEWED')->label('تمت المراجعة')->default(false),
                                        TextInput::make('REVIEW_BY')->label('المراجع')->numeric(),
                                        DateTimePicker::make('REVIEW_ON')->label('تاريخ المراجعة'),
                                        TextInput::make('REJECT_REASON')->label('سبب الرفض'),
                                        Toggle::make('SECOND_REVIEWED')->label('مراجعة ثانية')->default(false),
                                        TextInput::make('SECOND_REVIEWED_BY')->label('المراجع الثاني')->numeric(),
                                        DateTimePicker::make('SECOND_REVIEWED_ON')->label('تاريخ المراجعة الثانية'),
                                        TextInput::make('SECOND_REJECT_REASON')->label('سبب الرفض الثاني'),
                                        Textarea::make('NOTE')->label('ملاحظات')->columnSpanFull(),
                                    ])->columns(3),
                            ])
                            ->columnSpan('full'),
                    ])->columnSpan(9),

                    // القسم الأيسر (عرض 3)
                    Grid::make(1)->schema([
                        Section::make('معلومات أساسية')
                            ->schema([
                                Select::make('STATUS')
                                    ->label('حالة الملف')
                                    ->options(\App\Enums\ApplicantStatus::class)
                                    ->default('NEW'),
                                Select::make('UNID')
                                    ->label('الجامعة')
                                    ->options(fn (Get $get) => $get('IS_CLEARING') ? University::pluck('U_NAME', 'UNID') : University::coordination()->pluck('U_NAME', 'UNID'))
                                    ->live()
                                    ->searchable()
                                    ->required(),
                                Placeholder::make('APPLICANT_IDENT_PLACEHOLDER')
                                    ->label('رقم التنسيق (المتقدم)')
                                    ->content(fn ($record) => $record?->APPLICANT_IDENT ?? 'جديد'),
                                Placeholder::make('applications_count')
                                    ->label('عدد التقديمات')
                                    ->content(fn ($record) => $record ? $record->applications()->count() : 0),
                                Toggle::make('IS_CLEARING')
                                    ->label('طالب مقاصة؟')
                                    ->default(false)
                                    ->live()
                                    ->required(),
                                Toggle::make('FREEZE')
                                    ->label('تجميد الملف')
                                    ->default(false),
                            ]),
                    ])->columnSpan(3),
                ])->columnSpan('full'),
            ]);
    }
}
