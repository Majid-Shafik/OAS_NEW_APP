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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
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

class ApplicantEditForm
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
                            \Filament\Schemas\Components\Tabs::make('Tabs')
                                ->tabs([
                                    \Filament\Schemas\Components\Tabs\Tab::make('بيانات شخصية')
                                        ->icon('heroicon-o-user')
                                        ->schema([
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
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)
                                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                                $set('FULL_NAME', trim($state . ' ' . $get('LAST_NAME')));
                                                            }),
                                                        TextInput::make('LAST_NAME')
                                                            ->label('اللقب')
                                                            ->live(onBlur: true)
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)
                                                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                                                $set('FULL_NAME', trim($get('FIRST_NAME') . ' ' . $state));
                                                            }),
                                                        TextInput::make('FULL_NAME')
                                                            ->label('الاسم الكامل')
                                                            ->readOnly()
                                                            ->required(),
                                                        TextInput::make('NATIONAL_NUMBER')->label('الرقم الوطني')->numeric(),
                                                        Select::make('GENDER')->label('الجنس')->options(\App\Models\ComboValue::getOptionsValuesByCode(6))->searchable()
                                                            ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)->dehydrated(),
                                                        DatePicker::make('DATE_OF_BIRTH')
                                                            ->label('تاريخ الميلاد')
                                                            ->default(now()->subYears(18)->format('Y-m-d'))
                                                            ->maxDate(now()->subYears(14))
                                                            ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)->dehydrated(),
                                                        TextInput::make('PLACE_OF_BIRTH')->label('محل الميلاد')
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),
                                                        Select::make('PROVINCE')->label('المحافظة')
                                                            ->options(fn() => Province::pluck('NAME', 'NAME')->filter(fn($v) => ! empty($v))->toArray())
                                                            ->afterStateHydrated(function (Set $set, $state) {
                                                                if (in_array($state, ['امانةالعاصمة', 'امانة العاصمه', 'أمانة العاصمه', 'امانة العاصمة'])) {
                                                                    $set('PROVINCE', 'أمانة العاصمة');
                                                                }
                                                            })
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


                                        ]),
                                    Tab::make('بيانات الثانوية')
                                        ->icon('heroicon-o-academic-cap')
                                        ->schema([
                                            Fieldset::make('بيانات الثانوية')
                                                ->hidden(fn(Get $get) => $get('hs_degree_not_approved') === true)
                                                ->columnSpanFull()
                                                ->schema(
                                                    [
                                                        TextInput::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')
                                                            ->hint('*سنة الشهادة لعام 2012/2011 هي: 2012')
                                                            ->numeric()->rule('digits:4')->live(onBlur: true)
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),
                                                        Select::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                                                            ->options(\App\Models\ComboValue::getOptionsValuesByCode(1))
                                                            ->live()
                                                            ->searchable()
                                                            ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)->dehydrated(),
                                                        TextInput::make('SEC_SCHOOL_NAME')->label('اسم المدرسة')
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),
                                                        TextInput::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس')->live(onBlur: true)
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),
                                                        TextInput::make('SEC_SCHOOL_MARK')
                                                            ->label('المجموع')
                                                            ->numeric()
                                                            ->lte('SEC_SCHOOL_OVERALLMARK')
                                                            ->live(onBlur: true)
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)
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
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)
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
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)
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
                                                            ->disabled(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1)->dehydrated(),

                                                        TextInput::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),

                                                        TextInput::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية')
                                                            ->readOnly(fn(Get $get) => ($get('is_searched') && !$get('is_not_found')) || $get('is_hs_degree_b') || $get('APPLICANT_TYPE') == 1),
                                                    ]
                                                )->columns(4),

                                        ]),
                                    Tab::make('بيانات المقاصة')
                                        ->icon('heroicon-o-document-check')
                                        ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get, $livewire) => ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                        ->schema(function (Get $get) {
                                            if ($get('is_not_found')) {
                                                return [
                                                    TextEntry::make('type_b_notice')
                                                        ->label('')
                                                        ->state(new \Illuminate\Support\HtmlString('
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
                                                                return \App\Models\Program::withoutGlobalScopes()->where('UNID', $unid)->where('FACULTY_IDENT', $faculty)->get()
                                                                    ->mapWithKeys(fn($p) => [$p->PROGRAM_IDENT => (string) ($p->PROGRAM_NAME ?? $p->PROGRAM_IDENT)]);
                                                            })
                                                            ->getOptionLabelUsing(fn ($value, \Filament\Schemas\Components\Utilities\Get $get) => (string) (\App\Models\Program::withoutGlobalScopes()->where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $get('FROM_FACULTY_IDENT'))->where('PROGRAM_IDENT', $value)->first()?->PROGRAM_NAME ?? $value))
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
                                    Tab::make('المرفقات')
                                        ->icon('heroicon-o-paper-clip')
                                        ->visible(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                            $isClearing = $record ? $record->IS_CLEARING?->value === 1 : $get('IS_CLEARING') == 1;
                                            return $isClearing || $get('APPLICANT_TYPE') == 2;
                                        })
                                        ->schema([
                                            FileUpload::make('secondary_certificate')
                                                ->label('صورة شهادة الثانوية')
                                                ->columnSpanFull()
                                                ->disk(config('legacy_attachments.disk', 'public'))
                                                ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])
                                                ->maxSize(1500)
                                                ->openable()
                                                ->imageEditor()
                                                ->downloadable()
                                                ->formatStateUsing(function ($record) {
                                                    if (!$record) return null;
                                                    $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                    $dbName = config("database.connections.{$activeConnection}.database");
                                                    $baseDir = config("legacy_attachments.systems.{$dbName}", config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}"));

                                                    // Check for JPG first, then PDF
                                                    $filePathJpg = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.jpg';
                                                    $filePathPdf = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.pdf';

                                                    if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePathJpg)) {
                                                        return [$filePathJpg];
                                                    }
                                                    if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($filePathPdf)) {
                                                        return [$filePathPdf];
                                                    }

                                                    $degreeB = \App\Models\HighSchoolDegreeBType::where('UNID', $record->UNID)
                                                        ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                                                        ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                                                        ->first();
                                                    if ($degreeB && $degreeB->SEC_SCHOOL_CERTIFICATE) {
                                                        $typeB_jpgPath = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $degreeB->SEC_SCHOOL_CERTIFICATE . '.jpg';
                                                        if (\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($typeB_jpgPath)) {
                                                            return [$typeB_jpgPath];
                                                        }
                                                    }

                                                    return [];
                                                })
                                                ->dehydrated(false)
                                                ->saveRelationshipsUsing(function (\Illuminate\Database\Eloquent\Model $record, $state) {
                                                    if (!$state) return;
                                                    $file = is_array($state) ? reset($state) : $state;
                                                    if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                        $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                        $dbName = config("database.connections.{$activeConnection}.database");
                                                        $baseDir = config("legacy_attachments.systems.{$dbName}", config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}"));
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
                                                ->visible(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                                    if ($get('APPLICANT_TYPE') == 1) return false;
                                                    $isClearing = $record ? $record->IS_CLEARING?->value === 1 : $get('IS_CLEARING') == 1;
                                                    return $get('APPLICANT_TYPE') == 2 || $isClearing;
                                                })
                                                ->required(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                                    if ($get('APPLICANT_TYPE') == 1) return false;
                                                    $isClearing = $record ? $record->IS_CLEARING?->value === 1 : $get('IS_CLEARING') == 1;
                                                    if ($get('is_hs_degree_b')) {
                                                        return false; // Not required if already pulled from Type B database
                                                    }
                                                    if ($get('is_searched') && !$get('is_not_found')) {
                                                        return false;
                                                    }
                                                    return $get('APPLICANT_TYPE') == 2 || $isClearing;
                                                }),
                                            Repeater::make('clearing_attachments_list')
                                                ->label('مرفقات المقاصة')
                                                ->addActionLabel('إضافة مرفق جديد')
                                                ->visible(fn(Get $get, ?\Illuminate\Database\Eloquent\Model $record) => ($record ? $record->IS_CLEARING?->value === 1 : $get('IS_CLEARING') == 1))
                                                ->schema([
                                                    Select::make('ATTACH_IDENT')
                                                        ->label('نوع المرفق')
                                                        ->options([
                                                            3 => 'كشف درجات الطالب',
                                                            4 => 'استمارة المقاصة',
                                                            5 => 'صورة الاستثناء ان وجد',
                                                        ])
                                                        ->required()
                                                        ->distinct()
                                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                                    FileUpload::make('FILE_PATH')
                                                        ->label('الملف')
                                                        ->openable()
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
                                                    $dbName = config("database.connections.{$activeConnection}.database");
                                                    $baseDir = config("legacy_attachments.systems.{$dbName}", config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}"));

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
                                                                'FILE_PATH' => [$filePath],
                                                            ];
                                                        }
                                                    }
                                                    $set('clearing_attachments_list', $attachments);
                                                })
                                                ->saveRelationshipsUsing(function ($record, $state) {
                                                    if (!is_array($state)) return;

                                                    $activeConnection = $record->getConnectionName() ?? config('database.default');
                                                    $dbName = config("database.connections.{$activeConnection}.database");
                                                    $baseDir = config("legacy_attachments.systems.{$dbName}", config("legacy_attachments.systems.{$activeConnection}", "uploads/{$activeConnection}"));

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




                                        ]),
                                ])
                                ->columnSpan('full'),
                        ])->columnSpan(12),

                        // القسم الأيسر (عرض 3)


                    ])->columnSpan('full'),
            ]);
    }
}
