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
                                                            ->label('الاسم الثلاثي')
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
                                                        TextInput::make('MOBILE_PHONE')->label('رقم الهاتف')->tel()->required(),
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
                                        ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get, $livewire) => ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
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
                                                    ->visible(fn(\Filament\Schemas\Components\Utilities\Get $get, $livewire) => ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                                    ->schema([
                                                        \Filament\Forms\Components\Select::make('FROM_COUNTRY_IDENT')
                                                            ->label('الدولة القادم منها')
                                                            ->options(fn() => \App\Models\Country::withoutGlobalScopes()->get()->mapWithKeys(fn($c) => [$c->COUNTRY_IDENT => (string) ($c->COUNTRY_NAME ?? $c->COUNTRY_IDENT)]))
                                                            ->getOptionLabelUsing(fn($value) => (string) (\App\Models\Country::withoutGlobalScopes()->find($value)?->COUNTRY_NAME ?? $value))
                                                            ->searchable()
                                                            ->required(),

                                                        \Filament\Forms\Components\Select::make('FROM_UNIV_IDENT')
                                                            ->label('الجامعة القادم منها')
                                                            ->options(fn() => \App\Models\University::withoutGlobalScopes()->clearing()->get()->mapWithKeys(fn($u) => [$u->UNID => (string) ($u->U_NAME ?? $u->UNID)]))
                                                            ->getOptionLabelUsing(fn($value) => (string) (\App\Models\University::withoutGlobalScopes()->find($value)?->U_NAME ?? $value))
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
                                                            ->getOptionLabelUsing(fn($value, \Filament\Schemas\Components\Utilities\Get $get) => (string) (\App\Models\Faculty::withoutGlobalScopes()->where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $value)->first()?->FACULTY_NAME ?? $value))
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
                                                            ->getOptionLabelUsing(fn($value, \Filament\Schemas\Components\Utilities\Get $get) => (string) (\App\Models\Program::withoutGlobalScopes()->where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $get('FROM_FACULTY_IDENT'))->where('PROGRAM_IDENT', $value)->first()?->PROGRAM_NAME ?? $value))
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
                                            $isClearing = $record ? ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value === 1 : in_array($record->IS_CLEARING, [1, '1'])) : in_array($get('IS_CLEARING'), [1, '1']);
                                            return $isClearing || $get('APPLICANT_TYPE') == 2 || ($record && $record->APPLICANT_TYPE == 2);
                                        })
                                        ->schema([
                                            Section::make('مرفقات طالب المقاصاة')
                                                ->description('يرجى إرفاق المستندات المطلوبة الخاصة بطلب المقاصاة بصيغة PDF')
                                                ->visible(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                                    return $record ? ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value === 1 : in_array($record->IS_CLEARING, [1, '1'])) : in_array($get('IS_CLEARING'), [1, '1']);
                                                })
                                                ->schema([
                                                    Grid::make(3)->schema([
                                                        FileUpload::make('clearing_attachment_grades')
                                                            ->label('كشف درجات الطالب (مرفق إجباري)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->disk(config('legacy_attachments.disk', 'public'))
                                                            ->directory(fn ($record) => "uploads/" . \App\Helpers\PortalHelper::getPortalPrefix() . "/images/attachments/grades")
                                                            ->getUploadedFileNameForStorageUsing(fn ($record) => "{$record->UNID}-{$record->APPLICANT_IDENT}.pdf")
                                                            ->maxSize(7500)
                                                            ->openable()
                                                            ->downloadable()
                                                            ->deletable()
                                                            ->formatStateUsing(function ($record) {
                                                                if (!$record) return null;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/grades/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                return \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($path) ? $path : null;
                                                            })
                                                            ->deleteUploadedFileUsing(function ($file, $record) {
                                                                if (!$record) return;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/grades/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
                                                                if ($disk->exists($path)) {
                                                                  $disk->delete($path);
                                                                }
                                                                \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
                                                                    ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                                                                    ->where('ATTACH_IDENT', 3)
                                                                    ->delete();
                                                            }),

                                                        FileUpload::make('clearing_attachment_form')
                                                            ->label('استمارة المقاصاة (مرفق إجباري)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->disk(config('legacy_attachments.disk', 'public'))
                                                            ->directory(fn ($record) => "uploads/" . \App\Helpers\PortalHelper::getPortalPrefix() . "/images/attachments/clearing")
                                                            ->getUploadedFileNameForStorageUsing(fn ($record) => "{$record->UNID}-{$record->APPLICANT_IDENT}.pdf")
                                                            ->maxSize(7500)
                                                            ->openable()
                                                            ->downloadable()
                                                            ->deletable()
                                                            ->formatStateUsing(function ($record) {
                                                                if (!$record) return null;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/clearing/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                return \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($path) ? $path : null;
                                                            })
                                                            ->deleteUploadedFileUsing(function ($file, $record) {
                                                                if (!$record) return;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/clearing/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
                                                                if ($disk->exists($path)) {
                                                                    $disk->delete($path);
                                                                }
                                                                \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
                                                                    ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                                                                    ->where('ATTACH_IDENT', 4)
                                                                    ->delete();
                                                            }),

                                                        FileUpload::make('clearing_attachment_exception')
                                                            ->label('صورة الاستثناء إن وجد (مرفق اختياري)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->disk(config('legacy_attachments.disk', 'public'))
                                                            ->directory(fn ($record) => "uploads/" . \App\Helpers\PortalHelper::getPortalPrefix() . "/images/attachments/exceptions")
                                                            ->getUploadedFileNameForStorageUsing(fn ($record) => "{$record->UNID}-{$record->APPLICANT_IDENT}.pdf")
                                                            ->maxSize(7500)
                                                            ->openable()
                                                            ->downloadable()
                                                            ->deletable()
                                                            ->formatStateUsing(function ($record) {
                                                                if (!$record) return null;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/exceptions/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                return \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($path) ? $path : null;
                                                            })
                                                            ->deleteUploadedFileUsing(function ($file, $record) {
                                                                if (!$record) return;
                                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                                $path = "uploads/{$portalPrefix}/images/attachments/exceptions/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                                $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
                                                                if ($disk->exists($path)) {
                                                                    $disk->delete($path);
                                                                }
                                                                \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
                                                                    ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                                                                    ->where('ATTACH_IDENT', 5)
                                                                    ->delete();
                                                            }),
                                                    ]),
                                                ]),

                                            Section::make('شهادة الثانوية العامة')
                                                ->visible(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                                    if ($get('APPLICANT_TYPE') == 1) return false;
                                                    $isClearing = $record ? ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value === 1 : in_array($record->IS_CLEARING, [1, '1'])) : in_array($get('IS_CLEARING'), [1, '1']);
                                                    return $get('APPLICANT_TYPE') == 2 || $isClearing || ($record && $record->APPLICANT_TYPE == 2);
                                                })
                                                ->schema([
                                                    Callout::make('تنبيه: صيغة الصورة المسموحة')
                                                        ->description('يُسمح فقط برفع صورة شهادة الثانوية بصيغة JPG (.jpg) وبحجم أقصاه 500 كيلوبايت.')
                                                        ->info()
                                                        ->columnSpanFull(),
                                                    FileUpload::make('secondary_certificate')
                                                        ->label('صورة شهادة الثانوية (إجباري للنوع B)')
                                                        ->columnSpanFull()
                                                        ->disk(config('legacy_attachments.disk', 'public'))
                                                        ->acceptedFileTypes(['image/jpeg'])
                                                        ->helperText('نوع الملف المسموح به: JPG (.jpg) فقط، الحجم الأقصى 500 كيلوبايت.')
                                                        ->validationMessages([
                                                            'accepted_file_types' => 'نوع الملف غير صالح، يجب أن تكون الصورة بصيغة JPG (.jpg) فقط.',
                                                            'required' => 'صورة شهادة الثانوية مطلوبة إجبارياً للمتقدمين من نوع شهادة (B).',
                                                        ])
                                                        ->directory(fn ($record) => "uploads/" . \App\Helpers\PortalHelper::getPortalPrefix() . "/images/attachments/secondary")
                                                        ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                                                            return "{$record->UNID}-{$record->APPLICANT_IDENT}.jpg";
                                                        })
                                                        ->maxSize(500)
                                                        ->openable()
                                                        ->imageEditor()
                                                        ->downloadable()
                                                        ->deletable()
                                                        ->formatStateUsing(function ($record) {
                                                            if (!$record) return null;
                                                            $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                            $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

                                                            $jpgPath = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->UNID}-{$record->APPLICANT_IDENT}.jpg";

                                                            if ($disk->exists($jpgPath)) {
                                                                return $jpgPath;
                                                            }

                                                            $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                                                                ->where('UNID', $record->UNID)
                                                                ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                                                                ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                                                                ->first();

                                                            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO) && !empty($record->SEC_SCHOOL_YEAR)) {
                                                                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                                                                    ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                                                                    ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                                                                    ->first();
                                                            }

                                                            if (!$degreeB && !empty($record->SEC_SCHOOL_SEATNO)) {
                                                                $degreeB = \App\Models\HighSchoolDegreeBType::withoutGlobalScopes()
                                                                    ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                                                                    ->first();
                                                            }

                                                            if ($degreeB && $degreeB->SEC_SCHOOL_CERTIFICATE) {
                                                                $cert = basename($degreeB->SEC_SCHOOL_CERTIFICATE, '.jpg');
                                                                $typeB_jpg = "uploads/{$portalPrefix}/images/attachments/secondary/{$cert}.jpg";

                                                                if ($disk->exists($typeB_jpg)) {
                                                                  return $typeB_jpg;
                                                                }
                                                            }

                                                            return null;
                                                        })
                                                        ->deleteUploadedFileUsing(function ($file, $record) {
                                                            if (!$record) return;
                                                            $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                            $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
                                                            $jpgPath = "uploads/{$portalPrefix}/images/attachments/secondary/{$record->UNID}-{$record->APPLICANT_IDENT}.jpg";
                                                            if ($disk->exists($jpgPath)) $disk->delete($jpgPath);
                                                            \App\Models\ApplicantAttachment::where('UNID', $record->UNID)
                                                                ->where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                                                                ->where('ATTACH_IDENT', 2)
                                                                ->delete();
                                                        })
                                                        ->required(function (Get $get, ?\Illuminate\Database\Eloquent\Model $record) {
                                                            $applicantType = $get('APPLICANT_TYPE') ?? $record?->APPLICANT_TYPE;
                                                            return (int)$applicantType === 2 || (bool)$get('is_hs_degree_b');
                                                        })
                                                        ->validationMessages([
                                                            'required' => 'صورة شهادة الثانوية مطلوبة إجبارياً للمتقدمين من نوع شهادة (B).',
                                                        ]),
                                                ]),
                                        ]),
                                ]),
                        ])->columnSpan(12),
                    ])->columnSpan('full'),
            ]);
    }
}
