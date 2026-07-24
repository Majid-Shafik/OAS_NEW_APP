<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Enums\ApplicantStatus;
use App\Enums\Gender;
use App\Models\Applicant;
use App\Models\Country;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Province;
use App\Models\University;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
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
                Section::make('البحث والتحقق')
                    ->schema([
                        TextInput::make('search_seatno')->label('رقم الجلوس')->numeric()->dehydrated(false),
                        TextInput::make('search_year')->label('عام التخرج')->numeric()->dehydrated(false),
                        TextInput::make('search_mark')->label('المجموع')->numeric()->dehydrated(false),
                        Actions::make([
                            Action::make('search')
                                ->label('بحث وتحقق')
                                ->icon('heroicon-o-magnifying-glass')
                                ->action(function (Get $get, Set $set, $livewire) {
                                    $seat = $get('search_seatno');
                                    $year = $get('search_year');
                                    $mark = intval($get('search_mark'));

                                    if (!$seat || !$year || !$mark) {
                                        \Filament\Notifications\Notification::make()->warning()->title('الرجاء إدخال جميع الحقول للبحث')->send();
                                        return;
                                    }

                                    // 1. البحث محلياً
                                    $existing = \App\Models\Applicant::where('SEC_SCHOOL_SEATNO', $seat)
                                        ->where('SEC_SCHOOL_YEAR', $year)
                                        ->where('SEC_SCHOOL_MARK', $mark)
                                        ->first();

                                    if ($existing) {
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('المتقدم موجود مسبقاً في النظام')
                                            ->actions([
                                                Action::make('view')
                                                    ->label('فتح ملف المتقدم')
                                                    ->url(\App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $existing]))
                                            ])
                                            ->send();
                                        return;
                                    }

                                    // 2. البحث عبر API الوزارة
                                    $found = false;

                                    $baseUrl = env('YEMEN_EXAM_API_URL', 'http://yemenexam.gov.ye/Result/Data/');
                                    $username = env('YEMEN_EXAM_API_USERNAME', 'HighEdu');
                                    $password = env('YEMEN_EXAM_API_PASSWORD', 'HighEdu@2020');

                                    foreach ([1, 2, 3] as $sec) {
                                        $url = "{$baseUrl}{$sec}?username={$username}&password={$password}&number={$seat}&year={$year}&total={$mark}";

                                        try {
                                            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                                            if ($response->successful()) {
                                                $data = $response->json();
                                                if (!empty($data) && count($data) > 0) {
                                                    $found = true;
                                                    $student = $data[0];

                                                    // التعبئة التلقائية
                                                    $set('is_searched', true);
                                                    $set('SEC_SCHOOL_SEATNO', $seat);
                                                    $set('SEC_SCHOOL_YEAR', $year);
                                                    $set('SEC_SCHOOL_MARK', $mark);
                                                    $set('FULL_NAME', $student['name'] ?? '');

                                                    $nameParts = explode(' ', trim($student['name'] ?? ''));
                                                    $set('FIRST_NAME', $nameParts[0] ?? '');
                                                    $set('LAST_NAME', end($nameParts) ?? '');

                                                    $set('SEC_SCHOOL_NAME', $student['idSchool'] ?? '');
                                                    $set('SEC_SCHOOL_RATE', $student['Rate'] ?? '');

                                                    // تحديد نوع الثانوية بناء على القسم
                                                    if ($sec == 1) {
                                                        $set('SEC_SCHOOL_TYPE', 'علمي');
                                                        $set('SEC_SCHOOL_OVERALLMARK', 800);
                                                    } elseif ($sec == 2) {
                                                        $set('SEC_SCHOOL_TYPE', 'ادبي');
                                                        $set('SEC_SCHOOL_OVERALLMARK', 800);
                                                    } elseif ($sec == 3) {
                                                        $set('SEC_SCHOOL_TYPE', 'علوم شرعي');
                                                        $set('SEC_SCHOOL_OVERALLMARK', 1700);
                                                    }

                                                    $set('GENDER', $student['gender'] ?? '');
                                                    $set('PLACE_OF_BIRTH', $student['city_birth'] ?? '');

                                                    if (isset($student['bod'])) {
                                                        $set('DATE_OF_BIRTH', date('Y-m-d', strtotime(str_replace('/', '-', $student['bod']))));
                                                    }

                                                    $cityAndProvince = explode('/', $student['city_study'] ?? '');
                                                    $lastPos = count($cityAndProvince) > 1 ? (count($cityAndProvince) - 1) : 0;
                                                    $set('SEC_SCHOOL_TERRITORY', trim($cityAndProvince[0] ?? ''));
                                                    $set('SEC_SCHOOL_PROVINCE', trim($cityAndProvince[$lastPos] ?? ''));
                                                    $set('SEC_SCHOOL_PLACE', $student['city_study'] ?? '');

                                                    $Territory = explode('/', $student['city_birth'] ?? '');
                                                    $Province = count($Territory) > 1 ? (count($Territory) - 1) : 0;
                                                    $set('TERRITORY', trim($Territory[0] ?? ''));
                                                    $set('PROVINCE', trim(trim($Territory[$Province] ?? '')));

                                                    $set('COUNTRY_NAME', $student['nationality'] ?? '');
                                                    $set('YEMEN_NATIONAL', ($student['nationality'] ?? '') == 'يمني' ? 1 : 0);
                                                    $set('is_not_found', false);

                                                    \Filament\Notifications\Notification::make()->success()->title('تم جلب البيانات من الوزارة بنجاح')->send();
                                                    break;
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            // Ignore and try next
                                        }
                                    }

                                    if (!$found) {
                                        $set('is_searched', true);
                                        $set('is_not_found', true);
                                        $set('IMPORTED', 2);
                                        $set('APPLICANT_TYPE', 2); // النوع B
                                        $set('SEC_SCHOOL_SEATNO', $seat);
                                        $set('SEC_SCHOOL_YEAR', $year);
                                        $set('SEC_SCHOOL_MARK', $mark);
                                        \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على البيانات، يرجى الإدخال يدوياً')->send();
                                    }
                                })
                        ])
                    ])
                    ->columns(4)
                    ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->hidden(fn(Get $get) => $get('is_searched') === true),

                Grid::make(12)
                    ->visible(fn(Get $get, $livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord || $get('is_searched') === true)
                    ->schema([
                        Hidden::make('is_searched')->dehydrated(false),
                        Hidden::make('is_not_found')->dehydrated(false),
                        Hidden::make('IMPORTED'),
                        Hidden::make('APPLICANT_TYPE'),
                        // القسم الأيمن الأكبر (عرض 9)
                        Grid::make(1)->schema([
                            Callout::make('تنبيه')
                                ->description('لم يتم العثور على البيانات في النظام أو بيانات الوزارة، يرجى إدخال بيانات المتقدم يدوياً أدناه.')
                                ->warning()
                                ->columnSpanFull()
                                ->visible(fn(Get $get) => $get('is_not_found') === true),
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
                                            Select::make('GENDER')->label('الجنس')->options(\App\Models\ComboValue::getOptionsValuesByCode(6))->searchable(),
                                            DatePicker::make('DATE_OF_BIRTH')->label('تاريخ الميلاد'),
                                            TextInput::make('PLACE_OF_BIRTH')->label('محل الميلاد'),
                                            Select::make('PROVINCE')->label('المحافظة')
                                                ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                ->live()
                                                ->searchable(),
                                            Select::make('TERRITORY')->label('المديرية')
                                                ->options(function (Get $get) {
                                                    $province = $get('PROVINCE');
                                                    $query = Applicant::distinct()->whereNotNull('TERRITORY');
                                                    if ($province) {
                                                        $query->where('PROVINCE', $province);
                                                    }

                                                    return $query->pluck('TERRITORY', 'TERRITORY')->filter(fn($v) => ! empty($v))->toArray();
                                                })
                                                ->searchable(),
                                            Select::make('COUNTRY_NAME')->label('الدولة')
                                                ->options(fn() => Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                ->default('اليمن')
                                                ->required()->searchable(),
                                            Select::make('IDENT_TYPE')->label('نوع الهوية')->options(\App\Models\ComboValue::getOptionsValuesByCode(7))->searchable(),
                                            TextInput::make('IDENT_NO')->label('رقم الهوية'),
                                            Toggle::make('YEMEN_NATIONAL')->label('جنسية يمنية')->required(),
                                            TextInput::make('EMAIL')->label('البريد الإلكتروني'),
                                            TextInput::make('MOBILE_PHONE')->label('رقم الهاتف'),
                                            Select::make('BLOOD_GROUP')->label('فصيلة الدم')->options(\App\Models\ComboValue::getOptionsValuesByCode(8))->searchable(),
                                        ])->columns(3),

                                    Tab::make('بيانات الثانوية')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            TextInput::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')->numeric(),
                                            Select::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                                                ->options(\App\Models\ComboValue::getOptionsValuesByCode(1))->searchable()
                                                ->searchable(),
                                            TextInput::make('SEC_SCHOOL_NAME')->label('اسم المدرسة'),
                                            TextInput::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس'),
                                            TextInput::make('SEC_SCHOOL_RATE')->label('المعدل')->numeric()->suffix('%'),
                                            TextInput::make('SEC_SCHOOL_MARK')->label('المجموع')->numeric(),
                                            TextInput::make('SEC_SCHOOL_OVERALLMARK')->label('المجموع الكلي')->numeric(),
                                            Select::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')
                                                ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                ->live()
                                                ->searchable(),
                                            Select::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')
                                                ->options(function (Get $get) {
                                                    $province = $get('SEC_SCHOOL_PROVINCE');
                                                    $query = Applicant::distinct()->whereNotNull('SEC_SCHOOL_TERRITORY');
                                                    if ($province) {
                                                        $query->where('SEC_SCHOOL_PROVINCE', $province);
                                                    }

                                                    return $query->pluck('SEC_SCHOOL_TERRITORY', 'SEC_SCHOOL_TERRITORY')->filter(fn($v) => ! empty($v))->toArray();
                                                })
                                                ->required()->searchable(),
                                            TextInput::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية'),
                                        ])->columns(3),

                                    Tab::make('بيانات المقاصة والقبول')
                                        ->icon('heroicon-o-document-check')
                                        ->schema([
                                            Select::make('APPLICANT_TYPE')
                                                ->label('نوع المتقدم')
                                                ->optionsFromConfig('applicant_type')
                                                ->default(1)
                                                ->required(),
                                            DatePicker::make('ADMITTED_ON')->label('تاريخ القبول'),
                                            Select::make('ADMITTED_FACULITY')->label('الكلية المقبول بها')->required()
                                                ->options(fn(Get $get) => Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT'))
                                                ->live()->searchable(),
                                            Select::make('ADMITTED_PROGRAM')->label('التخصص المقبول به')->required()
                                                ->options(fn(Get $get) => Program::where('UNID', $get('UNID'))
                                                    ->where('FACULTY_IDENT', $get('ADMITTED_FACULITY'))
                                                    ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT'))
                                                ->searchable(),
                                            TextInput::make('ADMITTED_OFFERING')->label('رقم العرض')->numeric(),
                                        ])
                                        ->columns(2)
                                        ->visible(fn($record, Get $get) => $get('IS_CLEARING') || $record?->IS_CLEARING),

                                    Tab::make('بيانات النظام')
                                        ->icon('heroicon-o-server')
                                        ->schema([
                                            DateTimePicker::make('RECORDDATE')->label('تاريخ التسجيل')->required(),
                                            TextInput::make('INSERTED_BY')->label('تم الإدخال بواسطة')->numeric()->default(-1)->required(),
                                            TextInput::make('LAST_UPDATED_BY')->label('آخر تحديث بواسطة')->numeric(),
                                            DateTimePicker::make('LAST_UPDATED_ON')->label('تاريخ آخر تحديث')->required(),
                                            TextInput::make('APPROVED_BY')->label('تم الاعتماد بواسطة')->numeric(),
                                            DateTimePicker::make('APPROVED_ON')->label('تاريخ الاعتماد'),
                                            Select::make('IMPORTED')
                                                ->label('طريقة الإدخال')
                                                ->optionsFromConfig('imported')
                                                ->default(1)
                                                ->required(),
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
                                        ->options(ApplicantStatus::class)
                                        ->default('NEW'),
                                    Select::make('UNID')
                                        ->label('الجامعة')
                                        ->options(fn(Get $get) => $get('IS_CLEARING') ? University::pluck('U_NAME', 'UNID') : University::coordination()->pluck('U_NAME', 'UNID'))
                                        ->live()
                                        ->searchable()
                                        ->required(),
                                    Placeholder::make('APPLICANT_IDENT_PLACEHOLDER')
                                        ->label('رقم التنسيق (المتقدم)')
                                        ->content(fn($record) => $record?->APPLICANT_IDENT ?? 'جديد'),
                                    Placeholder::make('applications_count')
                                        ->label('عدد التقديمات')
                                        ->content(fn($record) => $record ? $record->applications()->count() : 0),
                                    Select::make('IS_CLEARING')
                                        ->label('نظام المقاصة')
                                        ->optionsFromConfig('is_clearing')
                                        ->default(0)
                                        ->live()
                                        ->required(),
                                    Toggle::make('FREEZE')
                                        ->label('تجميد الملف')
                                        ->onColor('danger')
                                        ->offColor('gray')
                                        ->onIcon('heroicon-m-check')
                                        ->offIcon('heroicon-m-minus')
                                        ->default(false),
                                ]),
                        ])->columnSpan(3),
                    ])->columnSpan('full'),
            ]);
    }
}
