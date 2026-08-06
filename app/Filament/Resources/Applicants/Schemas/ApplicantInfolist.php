<?php

namespace App\Filament\Resources\Applicants\Schemas;

use App\Filament\Traits\HasMinistryRefreshAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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
                                    ->icon('heroicon-o-document-check')
                                    ->visible(fn(\Illuminate\Database\Eloquent\Model $record, $livewire) => ($record->IS_CLEARING instanceof \App\Enums\IsClearingType ? $record->IS_CLEARING->value == 1 : in_array($record->IS_CLEARING, [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                                    ->schema([
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
                                        
                                        Fieldset::make('نتائج المراجعة')
                                            ->schema([
                                                \Filament\Infolists\Components\RepeatableEntry::make('review_results')
                                                    ->label('')
                                                    ->getStateUsing(function ($record) {
                                                        if (!$record) return [];
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
                                            ])->columnSpanFull(),
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
                                    ->visible(fn($record) => $record?->APPLICANT_TYPE == 2 || $record?->IS_CLEARING?->value === 1)
                                    ->schema([
                                        \Filament\Infolists\Components\RepeatableEntry::make('applicant_attachments')
                                            ->label('')
                                            ->getStateUsing(function ($record) {
                                                if (!$record) return [];
                                                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                                                $baseDir = "uploads/{$portalPrefix}";
                                                $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));

                                                $attachments = [];

                                                if ($record->APPLICANT_TYPE == 2) {
                                                    $jpgPath = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.jpg';
                                                    $pdfPath = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $record->UNID . '-' . $record->APPLICANT_IDENT . '.pdf';

                                                    $degreeB = \App\Models\HighSchoolDegreeBType::where('UNID', $record->UNID)
                                                        ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                                                        ->where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                                                        ->first();

                                                    $typeB_jpgPath = null;
                                                    if ($degreeB && $degreeB->SEC_SCHOOL_CERTIFICATE) {
                                                        $typeB_jpgPath = rtrim($baseDir, '/') . '/images/attachments/secondary/' . $degreeB->SEC_SCHOOL_CERTIFICATE . '.jpg';
                                                    }

                                                    if ($disk->exists($jpgPath)) {
                                                        $attachments[] = [
                                                            'title' => 'الشهادة الثانوية',
                                                            'url' => $disk->url($jpgPath),
                                                            'is_pdf' => false,
                                                        ];
                                                    } elseif ($disk->exists($pdfPath)) {
                                                        $attachments[] = [
                                                            'title' => 'الشهادة الثانوية',
                                                            'url' => $disk->url($pdfPath),
                                                            'is_pdf' => true,
                                                        ];
                                                    } elseif ($typeB_jpgPath && $disk->exists($typeB_jpgPath)) {
                                                        $attachments[] = [
                                                            'title' => 'الشهادة الثانوية (أساسي)',
                                                            'url' => route('high-school.certificate.download', $degreeB->SS_IDENT),
                                                            'is_pdf' => false,
                                                        ];
                                                    }
                                                }

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
                                                            ];
                                                        }
                                                    }
                                                }

                                                // Pre-render HTML for RepeatableEntry
                                                foreach ($attachments as &$att) {
                                                    if ($att['is_pdf']) {
                                                        $att['html'] = '<a href="' . $att['url'] . '" target="_blank" class="flex flex-col items-center justify-center p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition text-center group">
                                                                            <img src="' . asset('storage/images/pdf.png') . '" class="w-12 h-12 group-hover:scale-110 transition-transform duration-200" alt="PDF" />
                                                                            <span class="mt-2 text-sm font-semibold text-primary-600">عرض ملف PDF</span>
                                                                        </a>';
                                                    } else {
                                                        $att['html'] = '<a href="' . $att['url'] . '" target="_blank" class="block border p-1 rounded hover:shadow-md transition bg-gray-50 text-center">
                                                                            <img src="' . $att['url'] . '" style="height: 120px; width: auto; margin: 0 auto; object-fit: contain;" />
                                                                        </a>';
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
                                    ->formatStateUsing(fn($state) => $state == 1 ? '(A)' : ($state == 2 ? '(B)' : $state))
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
