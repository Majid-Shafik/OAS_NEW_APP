<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Filament\Traits\HasMinistryRefreshAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ApplicantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Callout::make('تنبيه: تعذر تسجيل الرغبة أثناء إضافة المتقدم')
                    ->description(function () {
                        $flash = session('offering_registration_failed');
                        if (is_array($flash)) {
                            return 'تم حفظ بيانات المتقدم بنجاح، ولكن لم يتم تسجيل الرغبة نظراً لـ: ' . ($flash['reasons'] ?? '');
                        }
                        return 'تم حفظ بيانات المتقدم بنجاح، ولكن لم يتم تسجيل الرغبة نظراً لـ: ' . (string)$flash;
                    })
                    ->warning()
                    ->visible(fn() => session()->has('offering_registration_failed'))
                    ->columnSpanFull(),

                Grid::make(12)->schema([
                    // القسم الأيمن الأكبر (عرض 9)
                    Grid::make(1)->schema([
                        Tabs::make('معلومات المتقدم')
                            ->tabs([
                                Tab::make('بيانات شخصية')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextEntry::make('FULL_NAME')->label('الاسم الكامل')->placeholder('-'),
                                        TextEntry::make('FIRST_NAME')->label('الاسم الأول'),
                                        TextEntry::make('LAST_NAME')->label('اللقب')->placeholder('-'),
                                        TextEntry::make('NATIONAL_NUMBER')->label('الرقم الوطني')->placeholder('-'),
                                        TextEntry::make('GENDER')->label('الجنس')->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(6, $state))->badge()->placeholder('-'),
                                        TextEntry::make('DATE_OF_BIRTH')->label('تاريخ الميلاد')->date()->placeholder('-'),
                                        TextEntry::make('PLACE_OF_BIRTH')->label('محل الميلاد')->placeholder('-'),
                                        TextEntry::make('PROVINCE')->label('المحافظة')->placeholder('-'),
                                        TextEntry::make('TERRITORY')->label('المديرية')->placeholder('-'),
                                        TextEntry::make('COUNTRY_NAME')->label('الدولة'),
                                        TextEntry::make('IDENT_TYPE')->label('نوع الهوية')->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(7, $state))->placeholder('-'),
                                        TextEntry::make('IDENT_NO')->label('رقم الهوية')->placeholder('-'),
                                        IconEntry::make('YEMEN_NATIONAL')->label('جنسية يمنية')->boolean(),
                                        TextEntry::make('EMAIL')->label('البريد الإلكتروني')->placeholder('-'),
                                        TextEntry::make('MOBILE_PHONE')->label('رقم الهاتف')->placeholder('-'),
                                        TextEntry::make('BLOOD_GROUP')->label('فصيلة الدم')->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(8, $state))->placeholder('-'),
                                    ])->columns(3),

                                Tab::make('بيانات الثانوية')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        Actions::make([
                                            HasMinistryRefreshAction::getApiRefreshAction('fetchApiTab')
                                                ->button(),
                                        ])
                                        ->columnSpanFull()
                                        ->alignEnd(),

                                        TextEntry::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(1, $state))->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_NAME')->label('اسم المدرسة')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_RATE')->label('المعدل')->placeholder('-')->suffix('%'),
                                        TextEntry::make('SEC_SCHOOL_MARK')->label('المجموع')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_OVERALLMARK')->label('المجموع الكلي')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية')->placeholder('-'),
                                    ])->columns(3),

                                Tab::make('بيانات المقاصة')
                                    ->icon(Heroicon::OutlinedDocumentCheck)
                                    ->visible(fn(\Illuminate\Database\Eloquent\Model $record, $livewire) => ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value == 1 : in_array($record->IS_CLEARING, [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                    ->schema([
                                        Tabs::make('تفاصيل المقاصة')
                                            ->columnSpanFull()
                                            ->tabs([
                                                Tab::make('بيانات المقاصة')
                                                    ->icon(Heroicon::OutlinedDocumentText)
                                                    ->schema([
                                                        Fieldset::make('نتائج المراجعة الأخيرة ')
                                                            ->schema([
                                                                RepeatableEntry::make('review_results')
                                                                    ->label('')
                                                                    ->getStateUsing(function ($record) {
                                                                        if (!$record) return [];
                                                                        $record->loadMissing(['reviewBy', 'secondReviewedBy']);
                                                                        $results = [];
                                                                        $results[] = [
                                                                            'phase' => 'المراجعة الأولى',
                                                                            'reviewer' => $record->reviewBy?->USER_NAME ?? '-',
                                                                            'status' => $record->REVIEWED,
                                                                            'date' => $record->REVIEW_ON,
                                                                            'reason' => $record->REJECT_REASON ?? '-',
                                                                        ];
                                                                        $results[] = [
                                                                            'phase' => 'المراجعة الثانية',
                                                                            'reviewer' => $record->secondReviewedBy?->USER_NAME ?? '-',
                                                                            'status' => $record->SECOND_REVIEWED,
                                                                            'date' => $record->SECOND_REVIEWED_ON,
                                                                            'reason' => $record->SECOND_REJECT_REASON ?? '-',
                                                                        ];
                                                                        return $results;
                                                                    })
                                                                    ->schema([
                                                                        TextEntry::make('phase')->label('مرحلة المراجعة')->weight('bold'),
                                                                        TextEntry::make('reviewer')->label('المراجعة بواسطة'),
                                                                        TextEntry::make('status')
                                                                            ->label('نتيجة المراجعة')
                                                                            ->badge()
                                                                            ->formatStateUsing(fn ($state) => match ((int) $state) {
                                                                                1 => 'معتمد',
                                                                                2 => 'مرفوض',
                                                                                default => 'قيد المراجعة',
                                                                            })
                                                                            ->color(fn ($state) => match ((int) $state) {
                                                                                1 => 'success',
                                                                                2 => 'danger',
                                                                                default => 'warning',
                                                                            }),
                                                                        TextEntry::make('date')->label('تاريخ المراجعة')->dateTime('Y-m-d H:i')->placeholder('-'),
                                                                        TextEntry::make('reason')->label('سبب الرفض'),
                                                                    ])
                                                                    ->columns(5)
                                                                    ->columnSpanFull()
                                                            ]),

                                                        Fieldset::make('بيانات الجامعة والتخصص التي جاء منها (المقاصاة)')
                                                            ->schema([
                                                                TextEntry::make('applicationsClearing.FROM_COUNTRY_NAME')->label('الدولة القادم منها')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.FROM_UNIV_NAME')->label('الجامعة القادم منها')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.FROM_FACULTY_NAME')->label('الكلية القادم منها')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.FROM_PROGRAM_NAME')->label('التخصص القادم منه')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.FROM_YEAR')->label('عام الانضمام')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.STUDY_LEVEL')->label('مستوى الدراسة')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.NO_STUDY_YEARS')->label('عدد سنوات الدراسة')->placeholder('-'),
                                                                TextEntry::make('applicationsClearing.MOVING_REASON')->label('سبب الانتقال')->placeholder('-')->columnSpanFull(),
                                                            ])->columns(4)->columnSpanFull(),
                                                    ]),

                                                Tab::make('المراجعات السابقة')
                                                    ->icon(Heroicon::OutlinedClock)
                                                    ->schema([
                                                        RepeatableEntry::make('monitorClearingReviewing')
                                                            ->label('سجل المراجعات السابقة')
                                                            ->table([
                                                                TableColumn::make('م')->alignCenter(),
                                                                TableColumn::make('النتيجة')->alignCenter(),
                                                                TableColumn::make('سبب الرفض'),
                                                                TableColumn::make('المراجع'),
                                                                TableColumn::make('تاريخ ووقت الحركة')->alignCenter(),
                                                            ])
                                                            ->getStateUsing(function ($record) {
                                                                if (!$record) return [];
                                                                $record->loadMissing('monitorClearingReviewing.reviewer');
                                                                return ($record->monitorClearingReviewing ?? collect())
                                                                    ->sortBy('RECORD_DATE')
                                                                    ->values()
                                                                    ->map(function ($item, $index) {
                                                                        return [
                                                                            'row_num' => $index + 1,
                                                                            'result' => $item->REVIEW_RESULTE,
                                                                            'reason' => $item->REJECT_REASON,
                                                                            'reviewer' => $item->reviewer?->USER_NAME ?? $item->REVIEW_BY ?? '-',
                                                                            'date' => $item->RECORD_DATE,
                                                                        ];
                                                                    });
                                                            })
                                                            ->schema([
                                                                TextEntry::make('row_num')->label('#')->badge()->color('gray')->alignCenter(),
                                                                TextEntry::make('result')
                                                                    ->label('النتيجة')
                                                                    ->badge()
                                                                    ->formatStateUsing(fn ($state) => match ($state) {
                                                                        'ACCEPT' => 'قبول (مرحلة 1)',
                                                                        'ACCEPT_SECOND' => 'قبول نهائي (مرحلة 2)',
                                                                        'REJECT' => 'رفض (مرحلة 1)',
                                                                        'REJECT_SECOND' => 'رفض نهائي (مرحلة 2)',
                                                                        'ReReview' => 'إعادة مراجعة (ReReview)',
                                                                        'CANECLING' => 'إلغاء',
                                                                        default => (string) ($state ?? '-'),
                                                                    })
                                                                    ->color(fn ($state) => match ($state) {
                                                                        'ACCEPT', 'ACCEPT_SECOND' => 'success',
                                                                        'REJECT', 'REJECT_SECOND' => 'danger',
                                                                        'CANECLING' => 'warning',
                                                                        'ReReview' => 'info',
                                                                        default => 'gray',
                                                                    })
                                                                    ->alignCenter(),
                                                                TextEntry::make('reason')
                                                                    ->label('سبب الرفض')
                                                                    ->placeholder('-'),
                                                                TextEntry::make('reviewer')
                                                                    ->label('المراجع')
                                                                    ->placeholder('-'),
                                                                TextEntry::make('date')
                                                                    ->label('تاريخ ووقت الحركة')
                                                                    ->dateTime('Y-m-d H:i:s')
                                                                    ->placeholder('-')
                                                                    ->alignCenter(),
                                                            ])
                                                            ->columnSpanFull(),
                                                    ]),
                                            ]),
                                    ]),
                                Tab::make('التخصص المقبول')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        TextEntry::make('APPLICANT_TYPE')->label('نوع المتقدم')->translateFromConfig('applicant_type')->placeholder('-'),
                                        TextEntry::make('ADMITTED_ON')->label('تاريخ القبول')->date()->placeholder('-'),
                                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية المقبول بها')->placeholder('-'),
                                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص المقبول به')->placeholder('-'),
                                        TextEntry::make('ADMITTED_OFFERING')->label('رقم العرض')->placeholder('-'),
                                    ])->columns(2),


                                Tab::make('بيانات النظام')
                                    ->icon('heroicon-o-server')
                                    ->schema([
                                        TextEntry::make('RECORDDATE')->label('تاريخ التسجيل')->dateTime(),
                                        TextEntry::make('insertedBy.USER_NAME')->label('تم الإدخال بواسطة'),
                                        TextEntry::make('lastUpdatedBy.USER_NAME')->label('آخر تحديث بواسطة')->placeholder('-'),
                                        TextEntry::make('LAST_UPDATED_ON')->label('تاريخ آخر تحديث')->dateTime()->placeholder('-'),
                                        TextEntry::make('approvedBy.USER_NAME')->label('تم الاعتماد بواسطة')->placeholder('-'),
                                        TextEntry::make('APPROVED_ON')->label('تاريخ الاعتماد')->dateTime()->placeholder('-'),
                                        TextEntry::make('IMPORTED')->label('طريقة الإدخال')->translateFromConfig('imported')->placeholder('-'),
                                        IconEntry::make('EXPORTED')->label('مُصدّر')->boolean(),

                                    ])->columns(3),

                                Tab::make('المرفقات')
                                    ->icon('heroicon-o-paper-clip')
                                    ->visible(function ($record) {
                                        if (!$record) return false;
                                        $isClearing = ($record->IS_CLEARING instanceof \App\Enums\IsClearingType) 
                                            ? ($record->IS_CLEARING->value === 1) 
                                            : in_array($record->IS_CLEARING, [1, '1']);
                                        return $record->APPLICANT_TYPE == 2 || $isClearing || !empty($record->SEC_SCHOOL_SEATNO);
                                    })
                                    ->schema([
                                        \Filament\Infolists\Components\RepeatableEntry::make('applicant_attachments')
                                            ->label('')
                                            ->getStateUsing(function ($record) {
                                                if (!$record) return [];
                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                $baseDir = "uploads/{$portalPrefix}";
                                                $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

                                                $attachments = [];

                                                // 1. Direct Secondary Certificate Attachment
                                                $jpgFile = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.jpg';

                                                if ($disk->exists($jpgFile)) {
                                                    $attachments[] = [
                                                        'title' => 'الشهادة الثانوية',
                                                        'url' => route('clearing.attachment.download', ['unid' => $record->UNID, 'applicant_ident' => $record->APPLICANT_IDENT, 'type' => 'secondary']),
                                                        'is_pdf' => false,
                                                        'type' => 'secondary',
                                                        'deletable' => true,
                                                    ];
                                                }

                                                // 2. High School Degree Type B (View Only)
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
                                                    $alreadyAdded = collect($attachments)->contains('title', 'شهادة الثانوية (نوع B)') || collect($attachments)->contains('title', 'الشهادة الثانوية');
                                                    if (!$alreadyAdded) {
                                                        $attachments[] = [
                                                            'title' => 'شهادة الثانوية (نوع B)',
                                                            'url' => route('high-school.certificate.download', $degreeB->SS_IDENT),
                                                            'is_pdf' => false,
                                                            'type' => 'type_b',
                                                            'deletable' => false, // عرض فقط للطلاب من النوع B
                                                        ];
                                                    }
                                                }

                                                // 3. Clearing Attachments
                                                $isClearing = ($record->IS_CLEARING instanceof \App\Enums\IsClearingType) 
                                                    ? ($record->IS_CLEARING->value === 1) 
                                                    : in_array($record->IS_CLEARING, [1, '1']);

                                                if ($isClearing) {
                                                    $clearingPaths = [
                                                        'كشف درجات الطالب' => 'grades',
                                                        'استمارة المقاصاة' => 'clearing',
                                                        'صورة الاستثناء ان وجد' => 'exceptions',
                                                    ];

                                                    foreach ($clearingPaths as $title => $folder) {
                                                        $filePath = rtrim($baseDir, '/') . "/images/attachments/{$folder}/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                                                        if ($disk->exists($filePath)) {
                                                            $attachments[] = [
                                                                'title' => $title,
                                                                'url' => route('clearing.attachment.download', ['unid' => $record->UNID, 'applicant_ident' => $record->APPLICANT_IDENT, 'type' => $folder]),
                                                                'is_pdf' => true,
                                                                'type' => $folder,
                                                                'deletable' => true,
                                                            ];
                                                        }
                                                    }
                                                }

                                                $canDelete = auth()->check() && auth()->user()->can('update', $record);

                                                // Pre-render HTML for RepeatableEntry
                                                foreach ($attachments as &$att) {
                                                    $deleteHtml = '';
                                                    if (!empty($att['deletable']) && $canDelete && !empty($att['type'])) {
                                                        $deleteUrl = route('clearing.attachment.delete', [
                                                            'unid' => $record->UNID,
                                                            'applicant_ident' => $record->APPLICANT_IDENT,
                                                            'type' => $att['type'],
                                                        ]);
                                                        $deleteHtml = '<form action="' . $deleteUrl . '" method="POST" onsubmit="return confirm(\'هل أنت متأكد من رغبتك في حذف هذا المرفق نهائياً؟\')" class="w-full mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                                                            ' . csrf_field() . '
                                                                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1 px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/50 dark:text-red-400 rounded transition-colors border border-red-200 dark:border-red-800 cursor-pointer">
                                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                </svg>
                                                                                <span>حذف المرفق</span>
                                                                            </button>
                                                                        </form>';
                                                    }

                                                    if ($att['is_pdf']) {
                                                        $att['html'] = '<div class="flex flex-col items-center justify-between p-3 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg text-center w-full">
                                                                            <a href="' . $att['url'] . '" target="_blank" class="flex flex-col items-center justify-center group w-full py-1">
                                                                                <img src="' . asset('storage/images/pdf.png') . '" class="group-hover:scale-105 transition-transform duration-200" style="max-height: 65px; max-width: 65px; width: auto; height: auto; object-fit: contain; margin: 0 auto;" alt="PDF" />
                                                                                <span class="mt-2 text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline flex items-center justify-center gap-1">
                                                                                    <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                                    عرض ملف PDF
                                                                                </span>
                                                                            </a>
                                                                            ' . $deleteHtml . '
                                                                        </div>';
                                                    } else {
                                                        $att['html'] = '<div class="flex flex-col items-center justify-between p-3 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-lg text-center w-full">
                                                                            <a href="' . $att['url'] . '" target="_blank" class="block w-full py-1 group">
                                                                                <img src="' . $att['url'] . '" class="rounded border border-gray-200 dark:border-gray-700 group-hover:shadow transition" style="max-height: 75px; width: auto; margin: 0 auto; object-fit: contain;" alt="صورة المرفق" />
                                                                                <span class="mt-2 block text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline">
                                                                                    عرض الصورة
                                                                                </span>
                                                                            </a>
                                                                            ' . $deleteHtml . '
                                                                        </div>';
                                                    }
                                                }

                                                return $attachments;
                                            })
                                            ->schema([
                                                \Filament\Infolists\Components\TextEntry::make('title')
                                                    ->label('نوع المرفق')
                                                    ->weight('bold'),
                                                \Filament\Infolists\Components\TextEntry::make('html')
                                                    ->label('')
                                                    ->html(),
                                            ])
                                            ->grid(3)
                                            ->columnSpanFull()
                                    ])
                            ])
                            ->columnSpan('full'),
                    ])->columnSpan(9),

                    // القسم الأيسر (عرض 3)
                    Grid::make(1)->schema([
                        Section::make('نتيجة المراجعة الأخيرة')
                            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                            ->visible(fn(\Illuminate\Database\Eloquent\Model $record, $livewire) => ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value == 1 : in_array($record->IS_CLEARING, [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                            ->schema([
                                TextEntry::make('REVIEWED')
                                    ->label('المراجعة الأولى')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                                        1 => 'معتمد',
                                        2 => 'مرفوض',
                                        default => 'قيد المراجعة',
                                    })
                                    ->color(fn ($state) => match ((int) $state) {
                                        1 => 'success',
                                        2 => 'danger',
                                        default => 'warning',
                                    })
                                    ->icon(fn ($state) => match ((int) $state) {
                                        1 => Heroicon::OutlinedCheckCircle,
                                        2 => Heroicon::OutlinedXCircle,
                                        default => Heroicon::OutlinedClock,
                                    }),

                                TextEntry::make('SECOND_REVIEWED')
                                    ->label('المراجعة الثانية')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                                        1 => 'معتمد',
                                        2 => 'مرفوض',
                                        default => 'قيد المراجعة',
                                    })
                                    ->color(fn ($state) => match ((int) $state) {
                                        1 => 'success',
                                        2 => 'danger',
                                        default => 'warning',
                                    })
                                    ->icon(fn ($state) => match ((int) $state) {
                                        1 => Heroicon::OutlinedCheckCircle,
                                        2 => Heroicon::OutlinedXCircle,
                                        default => Heroicon::OutlinedClock,
                                    }),
                            ])
                            ->inlineLabel()
                            ->extraAttributes([
                                'class' => '[&.fi-in-section-block .fi-in-entry-label]:!whitespace-nowrap'
                            ]),

                        Section::make('معلومات أساسية')
                            ->schema([
                                TextEntry::make('APPLICANT_IDENT')
                                    ->label('رقم التنسيق (المتقدم)')
                                    ->placeholder('-'),
                                TextEntry::make('university.U_NAME')
                                    ->label('الجامعة')
                                    ->placeholder('-'),
                                TextEntry::make('STATUS')
                                    ->label('حالة الملف')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('APPLICANT_TYPE')
                                    ->label('نوع الطالب')
                                    ->formatStateUsing(fn($state) => match ((int)$state) {
                                        1 => '(A)',
                                        2 => '(B)',
                                        3 => '(A*)',
                                        default => $state ? "({$state})" : '-',
                                    })
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('applications_count')
                                    ->label('عدد التقديمات')
                                    ->state(fn($record) => $record->applications()->count())
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('IS_CLEARING')
                                    ->label('نظام المقاصة')
                                    ->badge(),
                                TextEntry::make('FREEZE')
                                    ->label('حالة التجميد')
                                    ->badge(),
                            ])
                            ->inlineLabel()
                            // يمكنك التحكم بعرض العمود للعنوان والقيمة هنا:
                            // افتراضياً في فيلامنت تكون النسبة الثلث (1/3) للعنوان والثلثين (2/3) للقيمة
                            // هنا استخدمنا !grid-cols-2 لنجعلها نصف بالنصف 50% للعنوان و 50% للقيمة
                            ->extraAttributes([
                                'class' => '[&_.fi-in-entry-has-inline-label]:!grid-cols-2 [&_.fi-in-entry-content-col]:!col-span-1'
                            ]),
                    ])->columnSpan(3),
                ])->columnSpan('full'),
            ]);
    }
}
