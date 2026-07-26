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
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ApplicantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                Grid::make(12)
                    ->schema([
                        Hidden::make('applicant_exists')->dehydrated(false),
                        Hidden::make('applicant_id')->dehydrated(false),
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
                            Wizard::make([
                                Step::make('البحث والتحقق')
                                    ->description('فحص رقم الجلوس وعام التخرج')
                                    ->icon('heroicon-o-magnifying-glass')
                                    ->schema([
                                        Callout::make('تعليمات الإضافة')
                                            ->description('يرجى إدخال رقم الجلوس وعام التخرج للثانوية العامة ثم النقر على زر "التالي". سيقوم النظام بالبحث عن بياناتك تلقائياً.')
                                            ->info()
                                            ->columnSpanFull(),
                                        Select::make('UNID')
                                            ->label('الجامعة')
                                            ->options(University::Coordination()->pluck('U_NAME', 'UNID'))
                                            ->searchable()
                                            ->live(),
                                        TextInput::make('search_seatno')->label('رقم الجلوس')->numeric()->dehydrated(false)->required(),
                                        TextInput::make('search_year')->label('عام التخرج')->numeric()->rule('digits:4')->dehydrated(false)->required(),
                                    ])
                                    ->columns(3)
                                    ->visible(fn($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                    ->afterValidation(function (Get $get, Set $set, $livewire) {
                                        $seat = $get('search_seatno');
                                        $year = $get('search_year');
                                        $unid = $get('UNID');

                                        // 1. البحث محلياً على مستوى الجامعة
                                        $existing = Applicant::where('SEC_SCHOOL_SEATNO', $seat)
                                            ->where('SEC_SCHOOL_YEAR', $year)
                                            ->where('UNID', $unid)
                                            ->first();

                                        if ($existing) {
                                            $set('is_searched', true);
                                            $set('is_not_found', false);
                                            $set('applicant_exists', true);
                                            $set('applicant_id', $existing->APPLICANT_IDENT);

                                            $set('SEC_SCHOOL_SEATNO', $existing->SEC_SCHOOL_SEATNO);
                                            $set('SEC_SCHOOL_YEAR', $existing->SEC_SCHOOL_YEAR);
                                            $set('SEC_SCHOOL_MARK', $existing->SEC_SCHOOL_MARK);
                                            $set('SEC_SCHOOL_OVERALLMARK', $existing->SEC_SCHOOL_OVERALLMARK);
                                            $set('SEC_SCHOOL_RATE', $existing->SEC_SCHOOL_RATE);
                                            $set('SEC_SCHOOL_TYPE', $existing->SEC_SCHOOL_TYPE);
                                            $set('SEC_SCHOOL_NAME', $existing->SEC_SCHOOL_NAME);
                                            $set('SEC_SCHOOL_PROVINCE', $existing->SEC_SCHOOL_PROVINCE);
                                            $set('SEC_SCHOOL_TERRITORY', $existing->SEC_SCHOOL_TERRITORY);
                                            $set('SEC_SCHOOL_PLACE', $existing->SEC_SCHOOL_PLACE);

                                            $set('FIRST_NAME', $existing->FIRST_NAME);
                                            $set('LAST_NAME', $existing->LAST_NAME);
                                            $set('FULL_NAME', $existing->FULL_NAME);
                                            $set('NATIONAL_NUMBER', $existing->NATIONAL_NUMBER);
                                            $set('GENDER', $existing->GENDER);
                                            $set('DATE_OF_BIRTH', $existing->DATE_OF_BIRTH);
                                            $set('PLACE_OF_BIRTH', $existing->PLACE_OF_BIRTH);
                                            $set('PROVINCE', $existing->PROVINCE);
                                            $set('TERRITORY', $existing->TERRITORY);
                                            $set('COUNTRY_NAME', $existing->COUNTRY_NAME);
                                            $set('IDENT_TYPE', $existing->IDENT_TYPE);
                                            $set('IDENT_NO', $existing->IDENT_NO);
                                            $set('YEMEN_NATIONAL', $existing->YEMEN_NATIONAL);
                                            $set('EMAIL', $existing->EMAIL);
                                            $set('MOBILE_PHONE', $existing->MOBILE_PHONE);
                                            $set('BLOOD_GROUP', $existing->BLOOD_GROUP);

                                            \Filament\Notifications\Notification::make()->success()->title('المتقدم موجود مسبقاً، تم جلب بياناته')->send();
                                            return;
                                        }

                                        // 2. البحث عبر API الوزارة
                                        $found = false;

                                        $baseUrl = env('YEMEN_EXAM_API_URL', 'http://yemenexam.gov.ye/Result/Data/');
                                        $username = env('YEMEN_EXAM_API_USERNAME', 'HighEdu');
                                        $password = env('YEMEN_EXAM_API_PASSWORD', 'HighEdu@2020');

                                        foreach ([1, 2, 3] as $sec) {
                                            // Removed &total=... based on user request (no mark input in step 1)
                                            $url = "{$baseUrl}{$sec}?username={$username}&password={$password}&number={$seat}&year={$year}";

                                            try {
                                                $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                                                if ($response->successful()) {
                                                    $data = $response->json();
                                                    if (!empty($data) && count($data) > 0) {
                                                        $found = true;
                                                        $student = $data[0];

                                                        // التعبئة التلقائية
                                                        $set('is_searched', true);
                                                        $set('is_not_found', false);
                                                        $set('applicant_exists', false);

                                                        $set('SEC_SCHOOL_SEATNO', $student['seat_number'] ?? $seat);
                                                        $set('SEC_SCHOOL_YEAR', $student['year'] ?? $year);
                                                        $set('SEC_SCHOOL_MARK', $student['total'] ?? '');
                                                        $set('FULL_NAME', $student['name'] ?? '');

                                                        $nameParts = explode(' ', trim($student['name'] ?? ''));
                                                        $set('FIRST_NAME', $nameParts[0] ?? '');
                                                        $set('LAST_NAME', end($nameParts) ?? '');

                                                        $set('SEC_SCHOOL_NAME', $student['school_name'] ?? '');
                                                        $set('SEC_SCHOOL_RATE', $student['rate'] ?? '');

                                                        // تحديد نوع الثانوية والمجموع الكلي
                                                        $type = $student['type'] ?? '';
                                                        if ($type === 'علمي' || $sec == 1) {
                                                            $set('SEC_SCHOOL_TYPE', 'علمي');
                                                            $set('SEC_SCHOOL_OVERALLMARK', 800);
                                                        } elseif ($type === 'أدبي' || $type === 'ادبي' || $sec == 2) {
                                                            $set('SEC_SCHOOL_TYPE', 'ادبي');
                                                            $set('SEC_SCHOOL_OVERALLMARK', 800);
                                                        } elseif (str_contains($type, 'شرعي') || $sec == 3) {
                                                            $set('SEC_SCHOOL_TYPE', 'علوم شرعي');
                                                            $set('SEC_SCHOOL_OVERALLMARK', 1700);
                                                        }

                                                        $gender = $student['gender'] ?? '';
                                                        if (in_array(trim($gender), ['ذكر', 'M', 'Male', '1'])) {
                                                            $set('GENDER', \App\Enums\Gender::Male->value);
                                                        } elseif (in_array(trim($gender), ['أنثى', 'F', 'Female', '2', 'انثى'])) {
                                                            $set('GENDER', \App\Enums\Gender::Female->value);
                                                        } else {
                                                            $set('GENDER', $gender);
                                                        }

                                                        $set('PLACE_OF_BIRTH', $student['birth_area'] ?? '');

                                                        if (isset($student['date_of_brith'])) {
                                                            $set('DATE_OF_BIRTH', date('Y-m-d', strtotime(str_replace('/', '-', $student['date_of_brith']))));
                                                        }

                                                        $set('SEC_SCHOOL_PROVINCE', $student['school_governorate'] ?? '');
                                                        $set('PROVINCE', $student['governorate'] ?? '');

                                                        $nationality = $student['nationality'] ?? '';
                                                        $set('COUNTRY_NAME', $nationality);
                                                        $set('YEMEN_NATIONAL', ($nationality == 'يمني' || $nationality == 'اليمن') ? 1 : 0);


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
                                            $set('applicant_exists', false);
                                            $set('IMPORTED', 2);
                                            $set('APPLICANT_TYPE', 2); // النوع B
                                            $set('SEC_SCHOOL_SEATNO', $seat);
                                            $set('SEC_SCHOOL_YEAR', $year);
                                            \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على البيانات، يرجى الإدخال يدوياً')->send();
                                        }
                                    }),

                                Step::make('بيانات الطالب')
                                    ->description('بيانات المؤهل الثانوي والمعلومات الشخصية')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextEntry::make('applicant_exists_warning')
                                            ->label('')
                                            ->hiddenLabel()
                                            ->content(function (Get $get) {
                                                $id = $get('applicant_id');
                                                if ($id) {
                                                    $existing = \App\Models\Applicant::find($id);
                                                    if ($existing) {
                                                        $url = \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $existing]);
                                                        return new \Illuminate\Support\HtmlString("<div class='p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300' role='alert'><span class='font-medium'>تنبيه:</span> المتقدم موجود مسبقاً في النظام. <a href='{$url}' target='_blank' class='font-bold underline'>انقر هنا لفتح ملفه</a></div>");
                                                    }
                                                }
                                                return '';
                                            })
                                            ->visible(fn(Get $get) => $get('applicant_exists') === true)
                                            ->columnSpanFull(),
                                        Fieldset::make('بيانات الثانوية')
                                            ->columnSpanFull()
                                            ->schema(
                                                [
                                                    TextInput::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')->numeric()->rule('digits:4')->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found')),
                                                    Select::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                                                        ->options(\App\Models\ComboValue::getOptionsValuesByCode(1))
                                                        ->live()
                                                        ->searchable()
                                                        ->disabled(fn(Get $get) => $get('is_searched') && !$get('is_not_found'))->dehydrated(),
                                                    TextInput::make('SEC_SCHOOL_NAME')->label('اسم المدرسة')
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found')),
                                                    TextInput::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس')->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found')),
                                                    TextInput::make('SEC_SCHOOL_MARK')
                                                        ->label('المجموع')
                                                        ->numeric()
                                                        ->lte('SEC_SCHOOL_OVERALLMARK')
                                                        ->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found'))
                                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                            $overall = floatval($get('SEC_SCHOOL_OVERALLMARK'));
                                                            $mark = floatval($state);
                                                            if ($overall > 0 && $mark > 0 && $mark <= $overall) {
                                                                $set('SEC_SCHOOL_RATE', round(($mark / $overall) * 100, 2));
                                                            }
                                                        }),
                                                    TextInput::make('SEC_SCHOOL_OVERALLMARK')
                                                        ->label('المجموع الكلي')
                                                        ->numeric()
                                                        ->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found'))
                                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                                            $overall = floatval($state);
                                                            $mark = floatval($get('SEC_SCHOOL_MARK'));
                                                            if ($overall > 0 && $mark > 0 && $mark <= $overall) {
                                                                $set('SEC_SCHOOL_RATE', round(($mark / $overall) * 100, 2));
                                                            }
                                                        }),
                                                    TextInput::make('SEC_SCHOOL_RATE')
                                                        ->label('المعدل')
                                                        ->numeric()
                                                        ->suffix('%')
                                                        ->readOnly(fn(Get $get) => $get('is_searched') && !$get('is_not_found'))
                                                        ->rules([
                                                            fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                                $mark = floatval($get('SEC_SCHOOL_MARK'));
                                                                $overall = floatval($get('SEC_SCHOOL_OVERALLMARK'));

                                                                if ($overall > 0 && $mark > 0 && $mark <= $overall) {
                                                                    $expectedRate = round(($mark / $overall) * 100, 2);
                                                                    $enteredRate = round(floatval($value), 2);

                                                                    if (abs($expectedRate - $enteredRate) > 0.1) {
                                                                        $fail("المعدل غير صحيح. يجب أن يكون ({$expectedRate}%).");
                                                                    }
                                                                }
                                                            },
                                                        ]),
                                                    Select::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')
                                                        ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                        ->live()
                                                        ->required()
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
                                                        ->searchable(),
                                                    TextInput::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية'),
                                                ]
                                            )->columns(4),
                                        Fieldset::make('بيانات المتقدم')
                                            ->columnSpanFull()
                                            ->columns(4)
                                            ->schema(
                                                [
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
                                                    TextInput::make('FULL_NAME')
                                                        ->label('الاسم الكامل')
                                                        ->readOnly()
                                                        ->required(),
                                                    TextInput::make('NATIONAL_NUMBER')->label('الرقم الوطني')->numeric(),
                                                    Select::make('GENDER')->label('الجنس')->options(\App\Models\ComboValue::getOptionsValuesByCode(6))->searchable(),
                                                    DatePicker::make('DATE_OF_BIRTH')
                                                        ->label('تاريخ الميلاد')
                                                        ->default(now()->subYears(18)->format('Y-m-d'))
                                                        ->maxDate(now()->subYears(14)),
                                                    TextInput::make('PLACE_OF_BIRTH')->label('محل الميلاد'),
                                                    Select::make('PROVINCE')->label('المحافظة')
                                                        ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                        ->live()
                                                        ->required()
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
                                                        ->required()
                                                        ->searchable(),
                                                    Select::make('COUNTRY_NAME')->label('الدولة')
                                                        ->options(fn() => Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                        ->default('اليمن')
                                                        ->required()->searchable(),
                                                    Select::make('IDENT_TYPE')
                                                        ->label('نوع الهوية')
                                                        ->options(\App\Models\ComboValue::getOptionsValuesByCode(7))
                                                        ->default(fn() => \App\Models\ComboValue::where('CODE', 7)->where('VALUE', 'like', '%بطاقة%')->value('VALUE') ?? \App\Models\ComboValue::where('CODE', 7)->first()?->VALUE)
                                                        ->searchable(),
                                                    TextInput::make('IDENT_NO')->label('رقم الهوية'),
                                                    Toggle::make('YEMEN_NATIONAL')->label('جنسية يمنية')->default(true)->required(),
                                                    TextInput::make('EMAIL')->label('البريد الإلكتروني'),
                                                    TextInput::make('MOBILE_PHONE')->label('رقم الهاتف'),
                                                    Select::make('BLOOD_GROUP')->label('فصيلة الدم')->options(\App\Models\ComboValue::getOptionsValuesByCode(8))->searchable(),

                                                ]
                                            ),



                                    ])->columns(3),

                                Step::make('بيانات المقاصة والقبول')
                                    ->description('تحديد نوع المتقدم والقبول في الكلية والتخصص')
                                    ->icon('heroicon-o-document-check')
                                    ->schema(\App\Filament\Schemas\CoordinationSchema::getSchema())
                                    ->columns(2),
                            ])
                                ->columnSpan('full'),
                        ])->columnSpan(12),

                        // القسم الأيسر (عرض 3)


                    ])->columnSpan('full'),
            ]);
    }
}
