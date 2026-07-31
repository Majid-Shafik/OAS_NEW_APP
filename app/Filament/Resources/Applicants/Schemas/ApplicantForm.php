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
use Filament\Forms\Components\FileUpload;
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
                        Hidden::make('hs_degree_not_approved')->dehydrated(false),
                        Hidden::make('hs_degree_id')->dehydrated(false),
                        Hidden::make('is_hs_degree_b')->dehydrated(false),
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
                                        Callout::make('تنبيه هام')
                                            ->description(function (Get $get) {
                                                $id = $get('hs_degree_id');
                                                if ($id) {
                                                    $url = \App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource::getUrl('view', ['record' => $id]);
                                                    return new \Illuminate\Support\HtmlString("الطالب موجود ضمن بيانات الثانوية من النوع B ولكنه غير مراجع. <a href='{$url}' target='_blank' class='font-bold underline'>انقر هنا لفتح ملفه</a>");
                                                }
                                                return '';
                                            })
                                            ->danger()
                                            ->visible(fn(Get $get) => $get('hs_degree_not_approved') === true)
                                            ->columnSpanFull(),

                                        Select::make('UNID')
                                            ->label('الجامعة')
                                            ->options(University::Coordination()->pluck('U_NAME', 'UNID'))
                                            ->default(function () {
                                                if (auth()->user()->UNID != 0) return auth()->user()->UNID;
                                                return session('selected_unid', 0) != 0 ? session('selected_unid') : null;
                                            })
                                            ->searchable()
                                            ->live(),
                                        TextInput::make('search_seatno')->label('رقم الجلوس')->numeric()->dehydrated(false)->required(),
                                        TextInput::make('search_year')->label('عام التخرج')
                                            ->hint('*سنة الشهادة لعام 2012/2011 هي: 2012')
                                            ->numeric()->rule('digits:4')->dehydrated(false)->required(),
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
                                            $set('SEC_SCHOOL_PLACE', $existing->SEC_SCHOOL_PLACE);
                                            $set('SEC_SCHOOL_PROVINCE', $existing->SEC_SCHOOL_PROVINCE);
                                            $set('SEC_SCHOOL_TERRITORY', $existing->SEC_SCHOOL_TERRITORY);

                                            $set('FULL_NAME', $existing->FULL_NAME);
                                            $set('FIRST_NAME', $existing->FIRST_NAME);
                                            $set('LAST_NAME', $existing->LAST_NAME);

                                            $set('GENDER', $existing->GENDER);
                                            $set('DATE_OF_BIRTH', $existing->DATE_OF_BIRTH ? (\Carbon\Carbon::parse($existing->DATE_OF_BIRTH)->format('Y-m-d')) : null);
                                            $set('PLACE_OF_BIRTH', $existing->PLACE_OF_BIRTH);
                                            $set('PROVINCE', $existing->PROVINCE);
                                            $set('TERRITORY', $existing->TERRITORY);
                                            $set('COUNTRY_NAME', $existing->COUNTRY_NAME);
                                            $set('YEMEN_NATIONAL', $existing->YEMEN_NATIONAL);
                                            $set('COUNTRY_NAME', $existing->COUNTRY_NAME);
                                            $set('YEMEN_NATIONAL', $existing->YEMEN_NATIONAL);
                                            $set('EMAIL', $existing->EMAIL);
                                            $set('MOBILE_PHONE', $existing->MOBILE_PHONE);
                                            
                                            $set('NATIONAL_NUMBER', $existing->NATIONAL_NUMBER);
                                            $set('IDENT_NO', $existing->IDENT_NO);
                                            $set('IDENT_TYPE', $existing->IDENT_TYPE);
                                            $set('BLOOD_GROUP', $existing->BLOOD_GROUP);
                                            $set('APPLICANT_TYPE', $existing->APPLICANT_TYPE);

                                            return;
                                        }

                                        // 2. البحث عبر API الوزارة
                                        $found = false;

                                        $apiService = new \App\Services\MinistryApiService();
                                        $student = null;
                                        
                                        // We pass 0 as total since it's not input at this step
                                        $data = $apiService->fetchStudentData($year, $seat, 0);
                                        
                                        if (!empty($data) && (isset($data['seat_number']) || (is_array($data) && count($data) > 0))) {
                                            $found = true;
                                            // Handle if API returns an array or single object
                                            $student = isset($data[0]) ? $data[0] : $data;

                                            // التعبئة التلقائية

                                                        // التعبئة التلقائية
                                                        $set('is_searched', true);
                                                        $set('is_not_found', false);
                                                        $set('applicant_exists', false);
                                                        $set('APPLICANT_TYPE', 1);

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
                                                        $sec = $student['section_id'] ?? 1; // Default or fallback if needed
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

                                                        // Map Governorate to exact DB string
                                                        $gov = trim($student['governorate'] ?? '');
                                                        if (in_array($gov, ['الامانة', 'الأمانة', 'امانة العاصمة', 'أمانة العاصمة'])) {
                                                            $gov = \App\Models\Province::where('NAME', 'like', '%امانة%')->orWhere('NAME', 'like', '%أمانة%')->value('NAME') ?? $gov;
                                                        } else {
                                                            $gov = \App\Models\Province::where('NAME', 'like', "%{$gov}%")->value('NAME') ?? $gov;
                                                        }
                                                        $set('PROVINCE', $gov);

                                                        // Map School Governorate to exact DB string
                                                        $secGov = trim($student['school_governorate'] ?? '');
                                                        if (in_array($secGov, ['الامانة', 'الأمانة', 'امانة العاصمة', 'أمانة العاصمة'])) {
                                                            $secGov = \App\Models\Province::where('NAME', 'like', '%امانة%')->orWhere('NAME', 'like', '%أمانة%')->value('NAME') ?? $secGov;
                                                        } else {
                                                            $secGov = \App\Models\Province::where('NAME', 'like', "%{$secGov}%")->value('NAME') ?? $secGov;
                                                        }
                                                        $set('SEC_SCHOOL_PROVINCE', $secGov);

                                                        // Map Nationality to exact DB string
                                                        $nationality = trim($student['nationality'] ?? '');
                                                        if (in_array($nationality, ['يمني', 'يمنيه', 'يمنية', 'اليمن'])) {
                                                            $nationality = \App\Models\Country::where('COUNTRY_NAME', 'like', '%يمن%')->value('COUNTRY_NAME') ?? 'اليمن';
                                                            $set('YEMEN_NATIONAL', 1);
                                                        } else {
                                                            $nationality = \App\Models\Country::where('COUNTRY_NAME', 'like', "%{$nationality}%")->value('COUNTRY_NAME') ?? $nationality;
                                                            $set('YEMEN_NATIONAL', 0);
                                                        }
                                                        $set('COUNTRY_NAME', $nationality);


                                                        // \Filament\Notifications\Notification::make()->success()->title('تم جلب البيانات من الوزارة بنجاح')->send();
                                        }

                                        if (!$found) {
                                            // 3. البحث في جدول الشهائد ب
                                            $hsDegree = \App\Models\HighSchoolDegreeBType::where('SEC_SCHOOL_SEATNO', $seat)
                                                ->where('SEC_SCHOOL_YEAR', $year)
                                                ->where('UNID', $unid)
                                                ->first();

                                            if ($hsDegree) {
                                                if ($hsDegree->APPROVED == 1) {
                                                    $found = true;

                                                    $set('is_searched', true);
                                                    $set('is_not_found', false);
                                                    $set('applicant_exists', false);
                                                    $set('hs_degree_not_approved', false);
                                                    $set('is_hs_degree_b', true);

                                                    $set('SEC_SCHOOL_SEATNO', $hsDegree->SEC_SCHOOL_SEATNO);
                                                    $set('SEC_SCHOOL_YEAR', $hsDegree->SEC_SCHOOL_YEAR);
                                                    $set('SEC_SCHOOL_MARK', $hsDegree->SEC_SCHOOL_MARK);
                                                    $set('SEC_SCHOOL_OVERALLMARK', $hsDegree->SEC_SCHOOL_OVERALLMARK);
                                                    $set('SEC_SCHOOL_RATE', $hsDegree->SEC_SCHOOL_RATE);
                                                    $set('SEC_SCHOOL_TYPE', $hsDegree->SEC_SCHOOL_TYPE);
                                                    $set('SEC_SCHOOL_NAME', $hsDegree->SEC_SCHOOL_NAME);
                                                    $set('SEC_SCHOOL_PLACE', $hsDegree->SEC_SCHOOL_PLACE);
                                                    $set('SEC_SCHOOL_PROVINCE', $hsDegree->SEC_SCHOOL_PROVINCE);
                                                    $set('SEC_SCHOOL_TERRITORY', $hsDegree->SEC_SCHOOL_TERRITORY);

                                                    $set('FULL_NAME', $hsDegree->STUDENT_NAME);
                                                    $nameParts = explode(' ', trim($hsDegree->STUDENT_NAME ?? ''));
                                                    $set('FIRST_NAME', $nameParts[0] ?? '');
                                                    $set('LAST_NAME', end($nameParts) ?? '');

                                                    if ($hsDegree->GENDER == 'ذكر') {
                                                        $set('GENDER', \App\Enums\Gender::Male->value);
                                                    } elseif ($hsDegree->GENDER == 'انثى' || $hsDegree->GENDER == 'أنثى') {
                                                        $set('GENDER', \App\Enums\Gender::Female->value);
                                                    } else {
                                                        $set('GENDER', $hsDegree->GENDER);
                                                    }

                                                    if ($hsDegree->DATE_OF_BIRTH) {
                                                        $set('DATE_OF_BIRTH', $hsDegree->DATE_OF_BIRTH->format('Y-m-d'));
                                                    }
                                                    $set('PLACE_OF_BIRTH', $hsDegree->PLACE_OF_BIRTH);
                                                    $set('PROVINCE', $hsDegree->PROVINCE);
                                                    $set('TERRITORY', $hsDegree->TERRITORY);
                                                    $set('COUNTRY_NAME', $hsDegree->COUNTRY_NAME);
                                                    $set('YEMEN_NATIONAL', $hsDegree->YEMEN_NATIONAL ? 1 : 0);
                                                    $set('EMAIL', $hsDegree->EMAIL);
                                                    $set('MOBILE_PHONE', $hsDegree->MOBILE_PHONE);

                                                    $set('IMPORTED', 2);
                                                    $set('APPLICANT_TYPE', 2);

                                                    // \Filament\Notifications\Notification::make()->success()->title('تم جلب البيانات من المعتمدة بنجاح')->send();
                                                } else {
                                                    // غير معتمد
                                                    $set('hs_degree_not_approved', true);
                                                    $set('hs_degree_id', $hsDegree->SS_IDENT);
                                                    $set('is_not_found', false);
                                                    $set('is_searched', true);
                                                    $found = true; // prevent the !$found fallback from overwriting this
                                                }
                                            }
                                        }

                                        if (!$found) {
                                            $set('is_searched', true);
                                            $set('is_not_found', true);
                                            $set('applicant_exists', false);
                                            $set('hs_degree_not_approved', false);
                                            $set('IMPORTED', 2);
                                            $set('APPLICANT_TYPE', 2); // النوع B
                                            $set('SEC_SCHOOL_SEATNO', $seat);
                                            $set('SEC_SCHOOL_YEAR', $year);
                                            // \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على البيانات، يرجى الإدخال يدوياً')->send();
                                        }
                                    }),

                                Step::make('بيانات الطالب')
                                    ->description('بيانات المؤهل الثانوي والمعلومات الشخصية')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        Callout::make('ملاحظة هامة!')
                                            ->description(function (Get $get) {
                                                $id = $get('applicant_id');
                                                if ($id) {
                                                    $existing = \App\Models\Applicant::find($id);
                                                    if ($existing) {
                                                        $url = \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $existing]);
                                                        return new \Illuminate\Support\HtmlString('
                                                            <div class="mb-4 text-base font-bold">
                                                                المتقدم موجود مسبقاً في النظام ببيانات معتمدة. يمكنك الاستمرار للتنسيق له في تخصص آخر.
                                                            </div>
                                                            <a href="'.$url.'" target="_blank" style="text-decoration: none;" class="fi-btn fi-btn-size-md fi-btn-color-success fi-btn-style-solid shadow-sm inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold outline-none transition duration-75 bg-success-600 text-white hover:bg-success-500">
                                                                فتح ملف الطالب في تبويب جديد
                                                            </a>
                                                        ');
                                                    }
                                                }
                                                return '';
                                            })
                                            ->success()
                                            ->visible(fn(Get $get) => $get('applicant_exists') === true)
                                            ->columnSpanFull(),

                                        Callout::make('يجب مراجعة واعتماد بيانات الطالب أولاً!')
                                            ->description(function (Get $get) {
                                                $id = $get('hs_degree_id');
                                                $url = $id ? \App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource::getUrl('view', ['record' => $id]) : '#';
                                                
                                                return new \Illuminate\Support\HtmlString('
                                                    <div class="mb-4 text-base">
                                                        هذا الطالب تم إدخال بياناته مسبقاً كشهادة ثانوية من النوع B، ولكنه لا يزال قيد المراجعة ولم يتم اعتماده بعد.<br>
                                                        لا يمكنك إكمال التنسيق له حتى يتم الاعتماد.
                                                    </div>
                                                    <a href="'.$url.'" style="text-decoration: none;" class="fi-btn fi-btn-size-md fi-btn-color-primary fi-btn-style-solid shadow-sm inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold outline-none transition duration-75 bg-primary-600 text-white hover:bg-primary-500">
                                                        الذهاب إلى ملف الطالب للمراجعة والاعتماد
                                                    </a>
                                                ');
                                            })
                                            ->warning()
                                            ->visible(fn(Get $get) => $get('hs_degree_not_approved') === true)
                                            ->columnSpanFull(),

                                        Callout::make('تم جلب البيانات من الوزارة بنجاح')
                                            ->success()
                                            ->visible(fn(Get $get) => $get('is_searched') === true && $get('is_not_found') === false && $get('is_hs_degree_b') !== true)
                                            ->columnSpanFull(),
                                            
                                        Callout::make('تم جلب البيانات من المعتمدة بنجاح')
                                            ->success()
                                            ->visible(fn(Get $get) => $get('is_searched') === true && $get('is_not_found') === false && $get('is_hs_degree_b') === true)
                                            ->columnSpanFull(),

                                        Fieldset::make('بيانات الثانوية')
                                            ->hidden(fn(Get $get) => $get('hs_degree_not_approved') === true)
                                            ->columnSpanFull()
                                            ->schema(
                                                [
                                                    TextInput::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')
                                                        ->hint('*سنة الشهادة لعام 2012/2011 هي: 2012')
                                                        ->numeric()->rule('digits:4')->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
                                                    Select::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                                                        ->options(\App\Models\ComboValue::getOptionsValuesByCode(1))
                                                        ->live()
                                                        ->searchable()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    TextInput::make('SEC_SCHOOL_NAME')->label('اسم المدرسة')
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
                                                    TextInput::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس')->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
                                                    TextInput::make('SEC_SCHOOL_MARK')
                                                        ->label('المجموع')
                                                        ->numeric()
                                                        ->lte('SEC_SCHOOL_OVERALLMARK')
                                                        ->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))
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
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))
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
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))
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
                                                        ->searchable()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    
                                                        TextInput::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),

                                                        TextInput::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية')
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
                                                    FileUpload::make('secondary_certificate')
                                                        ->label('صورة شهادة الثانوية')
                                                        ->columnSpanFull()
                                                        ->disk(config('legacy_attachments.disk', 'public'))
                                                        ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                                                        ->maxSize(1500)
                                                        ->openable()
                                                        ->imageEditor()
                                                        ->downloadable()
                                                        ->formatStateUsing(function ($record) {
                                                            if (!$record) return null;
                                                            $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                            $baseDir = config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}");
                                                            
                                                            // Check for JPG first, then PDF
                                                            $filePathJpg = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.jpg';
                                                            $filePathPdf = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.pdf';
                                                            
                                                            if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePathJpg)) {
                                                                return $filePathJpg;
                                                            }
                                                            return \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePathPdf) ? $filePathPdf : null;
                                                        })
                                                        ->dehydrated(false)
                                                        ->saveRelationshipsUsing(function (\Illuminate\Database\Eloquent\Model $record, $state) {
                                                            if (!$state) return;
                                                            $file = is_array($state) ? reset($state) : $state;
                                                            if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                                $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                                $baseDir = config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}");
                                                                $path = rtrim($baseDir, '/') . '/images/attachments/secondary';
                                                                $extension = $file->getClientOriginalExtension();
                                                                $filename = "{$record->UNID}-{$record->APPLICANT_IDENT}.{$extension}";
                                                                $file->storeAs($path, $filename, config('legacy_attachments.disk', 'public'));
                                                                \App\Models\ApplicantAttachment::updateOrCreate(
                                                                    ['UNID' => $record->UNID, 'APPLICANT_IDENT' => $record->APPLICANT_IDENT, 'ATTACH_IDENT' => 2],
                                                                    []
                                                                );
                                                            }
                                                        })
                                                        ->visible(function (Get $get) {
                                                            if ($get('is_hs_degree_b')) {
                                                                return false;
                                                            }
                                                            if ($get('is_searched') && !$get('is_not_found')) {
                                                                return false;
                                                            }
                                                            return $get('APPLICANT_TYPE') == 2 || $get('IS_CLEARING') == 1;
                                                        })
                                                        ->required(function (Get $get) {
                                                            if ($get('is_hs_degree_b')) {
                                                                return false;
                                                            }
                                                            if ($get('is_searched') && !$get('is_not_found')) {
                                                                return false;
                                                            }
                                                            return $get('APPLICANT_TYPE') == 2;
                                                        }),
                                                ]
                                            )->columns(4),
                                        Fieldset::make('بيانات المتقدم')
                                            ->hidden(fn(Get $get) => $get('hs_degree_not_approved') === true)
                                            ->columnSpanFull()
                                            ->columns(4)
                                            ->schema(
                                                [
                                                    TextInput::make('FIRST_NAME')
                                                        ->label('الاسم الأول')
                                                        ->required()
                                                        ->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $set('FULL_NAME', trim($state . ' ' . $get('LAST_NAME')));
                                                        }),
                                                    TextInput::make('LAST_NAME')
                                                        ->label('اللقب')
                                                        ->live(onBlur: true)
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))
                                                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                            $set('FULL_NAME', trim($get('FIRST_NAME') . ' ' . $state));
                                                        }),
                                                    TextInput::make('FULL_NAME')
                                                        ->label('الاسم الكامل')
                                                        ->readOnly()
                                                        ->required(),
                                                    TextInput::make('NATIONAL_NUMBER')->label('الرقم الوطني')->numeric(),
                                                    Select::make('GENDER')->label('الجنس')->options(\App\Models\ComboValue::getOptionsValuesByCode(6))->searchable()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    DatePicker::make('DATE_OF_BIRTH')
                                                        ->label('تاريخ الميلاد')
                                                        ->default(now()->subYears(18)->format('Y-m-d'))
                                                        ->maxDate(now()->subYears(14))
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    TextInput::make('PLACE_OF_BIRTH')->label('محل الميلاد')
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
                                                    Select::make('PROVINCE')->label('المحافظة')
                                                        ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                        ->live()
                                                        ->required()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated()
                                                        ->searchable(),
                                                                      TextInput::make('TERRITORY')->label(' المديرية')
                                                        ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b')),
 
                                                    Select::make('COUNTRY_NAME')->label('الدولة')
                                                        ->options(fn() => Country::pluck('COUNTRY_NAME', 'COUNTRY_NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                        ->default('اليمن')
                                                        ->required()->searchable()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    Select::make('IDENT_TYPE')
                                                        ->label('نوع الهوية')
                                                        ->options(\App\Models\ComboValue::getOptionsValuesByCode(7))
                                                        ->default(fn() => \App\Models\ComboValue::where('CODE', 7)->where('VALUE', 'like', '%بطاقة%')->value('VALUE') ?? \App\Models\ComboValue::where('CODE', 7)->first()?->VALUE)
                                                        ->searchable()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    TextInput::make('IDENT_NO')->label('رقم الهوية'),
                                                    Toggle::make('YEMEN_NATIONAL')->label('جنسية يمنية')->default(true)->required()
                                                        ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b'))->dehydrated(),
                                                    TextInput::make('EMAIL')->label('البريد الإلكتروني'),
                                                    TextInput::make('MOBILE_PHONE')->label('رقم الهاتف'),
                                                    Select::make('BLOOD_GROUP')->label('فصيلة الدم')->options(\App\Models\ComboValue::getOptionsValuesByCode(8))->searchable(),

                                                ]
                                            ),

                                        \Filament\Forms\Components\Repeater::make('clearing_attachments_list')
                                            ->label('مرفقات المقاصة')
                                            ->addActionLabel('إضافة مرفق جديد')
                                            ->visible(fn(Get $get) => $get('IS_CLEARING') == 1)
                                            ->schema([
                                                \Filament\Forms\Components\Select::make('ATTACH_IDENT')
                                                    ->label('نوع المرفق')
                                                    ->options([
                                                        3 => 'كشف درجات الطالب',
                                                        4 => 'استمارة المقاصة',
                                                        5 => 'صورة الاستثناء ان وجد',
                                                    ])
                                                    ->required()
                                                    ->distinct()
                                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                                \Filament\Forms\Components\FileUpload::make('FILE_PATH')
                                                    ->label('الملف')
                                                    ->disk(config('legacy_attachments.disk', 'public'))
                                                    ->acceptedFileTypes(['application/pdf'])
                                                    ->maxSize(7500)
                                                    ->required(),
                                            ])
                                            ->columns(2)
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function ($component, $record, $set) {
                                                if (!$record) return;
                                                $attachments = [];
                                                $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                $baseDir = config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}");
                                                
                                                $paths = [
                                                    3 => '/images/attachments/grades/',
                                                    4 => '/images/attachments/clearing/',
                                                    5 => '/images/attachments/exceptions/',
                                                ];
                                                
                                                foreach ($paths as $ident => $path) {
                                                    $filePath = rtrim($baseDir, '/') . $path . $record->UNID . '-' . $record->APPLICANT_IDENT . '.pdf';
                                                    if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePath)) {
                                                        $attachments[(string)$ident] = [
                                                            'ATTACH_IDENT' => $ident,
                                                            'FILE_PATH' => $filePath,
                                                        ];
                                                    }
                                                }
                                                $set('clearing_attachments_list', $attachments);
                                            })
                                            ->saveRelationshipsUsing(function ($record, $state) {
                                                if (!is_array($state)) return;
                                                
                                                $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                $baseDir = config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}");
                                                
                                                $paths = [
                                                    3 => '/images/attachments/grades',
                                                    4 => '/images/attachments/clearing',
                                                    5 => '/images/attachments/exceptions',
                                                ];
                                                
                                                $keptIdents = [];
                                                
                                                foreach ($state as $item) {
                                                    $ident = $item['ATTACH_IDENT'] ?? null;
                                                    $file = $item['FILE_PATH'] ?? null;
                                                    if (!$ident || !$file) continue;
                                                    
                                                    $keptIdents[] = $ident;
                                                    $file = is_array($file) ? reset($file) : $file;
                                                    
                                                    if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                        $path = rtrim($baseDir, '/') . $paths[$ident];
                                                        $filename = "{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                        $file->storeAs($path, $filename, config('legacy_attachments.disk', 'public'));
                                                        
                                                        \App\Models\ApplicantAttachment::updateOrCreate(
                                                            ['UNID' => $record->UNID, 'APPLICANT_IDENT' => $record->APPLICANT_IDENT, 'ATTACH_IDENT' => $ident],
                                                            []
                                                        );
                                                    }
                                                }
                                                
                                                $allPossible = [3, 4, 5];
                                                $toRemove = array_diff($allPossible, $keptIdents);
                                                
                                                foreach ($toRemove as $identToRemove) {
                                                    \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
                                                        ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                                                        ->where('ATTACH_IDENT', $identToRemove)
                                                        ->delete();
                                                        
                                                    $filePath = rtrim($baseDir, '/') . $paths[$identToRemove] . "/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                    if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePath)) {
                                                        \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->delete($filePath);
                                                    }
                                                }
                                            })->columnSpanFull(),



                                    ])->columns(3),

                                Step::make('بيانات المقاصة')
                                    ->description('تحديد نوع المتقدم والقبول في الكلية والتخصص')
                                    ->icon('heroicon-o-document-check')
                                    ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get, $livewire) => 
                                        $get('hs_degree_not_approved') !== true && 
                                        (($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                    )
                                    ->schema(function (Get $get) {
                                        if ($get('is_not_found')) {
                                            return [
                                                Placeholder::make('type_b_notice')
                                                    ->label('')
                                                    ->content(new \Illuminate\Support\HtmlString('
                                                        <div class="p-6 bg-blue-50 border-r-4 border-blue-500 rounded-lg shadow-sm" style="direction: rtl;">
                                                            <div class="flex items-center mb-4">
                                                                <svg class="w-8 h-8 text-blue-600 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                <h3 class="text-xl font-bold text-blue-800">حفظ كشهادة (نوع B)</h3>
                                                            </div>
                                                            <p class="text-blue-700 text-lg mb-2">
                                                                نظراً لعدم وجود بيانات هذا الطالب في النظام أو في بيانات الوزارة، سيتم حفظ هذه البيانات كشهادة ثانوية من النوع B.
                                                            </p>
                                                            <p class="text-blue-700 text-lg font-bold">
                                                                يرجى النقر على زر <span class="bg-primary-600 text-white px-2 py-1 rounded text-sm mx-1">تأكيد / إنشاء</span> بالأسفل لحفظ البيانات. سيتم بعد ذلك نقلك تلقائياً إلى شاشة عرض ملف الطالب لمراجعة بياناته واعتمادها حتى تستطيع تسجيله في تخصص.
                                                            </p>
                                                        </div>
                                                    ')),
                                            ];
                                        }
                                        return [
                                            \Filament\Schemas\Components\Fieldset::make('applicationsClearing')
                                                ->relationship('applicationsClearing')
                                                ->label('بيانات الجامعة والتخصص التي جاء منها (المقاصاة)')
                                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get, $livewire) => ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                                ->schema([
                                                    \Filament\Forms\Components\Select::make('FROM_COUNTRY_IDENT')
                                                        ->label('الدولة القادم منها')
                                                        ->options(fn() => \App\Models\Country::withoutGlobalScopes()->get()->mapWithKeys(fn($c) => [$c->COUNTRY_IDENT => (string) ($c->COUNTRY_NAME ?? $c->COUNTRY_IDENT)]))
                                                        ->getOptionLabelUsing(fn ($value) => (string) (\App\Models\Country::withoutGlobalScopes()->find($value)?->COUNTRY_NAME ?? $value))
                                                        ->searchable()
                                                        ->required(),
                                                    
                                                    \Filament\Forms\Components\Select::make('FROM_UNIV_IDENT')
                                                        ->label('الجامعة القادم منها')
                                                        ->options(fn() => \App\Models\University::withoutGlobalScopes()->clearing()->get()->mapWithKeys(fn($u) => [$u->UNID => (string) ($u->U_NAME ?? $u->UNID)]))
                                                        ->getOptionLabelUsing(fn ($value) => (string) (\App\Models\University::withoutGlobalScopes()->find($value)?->U_NAME ?? $value))
                                                        ->searchable()
                                                        ->live()
                                                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, $state) {
                                                            $set('FROM_FACULTY_IDENT', null);
                                                            $set('FROM_PROGRAM_IDENT', null);
                                                        })
                                                        ->required(),
                                
                                                    \Filament\Forms\Components\Select::make('FROM_FACULTY_IDENT')
                                                        ->label('الكلية القادم منها')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $unid = $get('FROM_UNIV_IDENT');
                                                            if (!$unid) return [];
                                                            return \App\Models\Faculty::withoutGlobalScopes()->where('UNID', $unid)->get()
                                                                ->mapWithKeys(fn($f) => [$f->FACULTY_IDENT => (string) ($f->FACULTY_NAME ?? $f->FACULTY_IDENT)]);
                                                        })
                                                        ->getOptionLabelUsing(fn ($value, \Filament\Schemas\Components\Utilities\Get $get) => (string) (\App\Models\Faculty::withoutGlobalScopes()->where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $value)->first()?->FACULTY_NAME ?? $value))
                                                        ->searchable()
                                                        ->live()
                                                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, \Filament\Schemas\Components\Utilities\Get $get, $state) {
                                                            $set('FROM_PROGRAM_IDENT', null);
                                                        })
                                                        ->required(),
                                
                                                    \Filament\Forms\Components\Select::make('FROM_PROGRAM_IDENT')
                                                        ->label('التخصص القادم منه')
                                                        ->options(function (\Filament\Schemas\Components\Utilities\Get $get) {
                                                            $unid = $get('FROM_UNIV_IDENT');
                                                            $faculty = $get('FROM_FACULTY_IDENT');
                                                            if (!$unid || !$faculty) return [];
                                                            return \App\Models\Program::where('UNID', $unid)->where('FACULTY_IDENT', $faculty)->get()
                                                                ->mapWithKeys(fn($p) => [$p->PROGRAM_IDENT => (string) ($p->PROGRAM_NAME ?? $p->PROGRAM_IDENT)]);
                                                        })
                                                        ->getOptionLabelUsing(fn ($value, \Filament\Schemas\Components\Utilities\Get $get) => (string) (\App\Models\Program::where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $get('FROM_FACULTY_IDENT'))->where('PROGRAM_IDENT', $value)->first()?->PROGRAM_NAME ?? $value))
                                                        ->searchable()
                                                        ->live()
                                                        ->required(),
                                                    \Filament\Forms\Components\TextInput::make('NO_STUDY_YEARS')->label('عدد سنوات الدراسة')->numeric(),
                                                    \Filament\Forms\Components\TextInput::make('STUDY_LEVEL')->label('مستوى الدراسة')->numeric(),
                                                    \Filament\Forms\Components\TextInput::make('FROM_YEAR')->label('عام الانضمام')->numeric(),
                                                    \Filament\Forms\Components\Textarea::make('MOVING_REASON')->label('سبب الانتقال')->required()->columnSpanFull(),
                                                ])
                                                ->columns(4)
                                                ->columnSpanFull(),
                                        ];
                                    })
                                    ->columns(2),
                            ])
                                ->columnSpan('full'),
                        ])->columnSpan(12),

                        // القسم الأيسر (عرض 3)


                    ])->columnSpan('full'),
            ]);
    }
}
