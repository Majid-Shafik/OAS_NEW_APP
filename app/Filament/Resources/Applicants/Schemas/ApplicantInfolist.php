<?php

namespace App\Filament\Resources\Applicants\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
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
                Grid::make(12)->schema([
                    // القسم الأيمن الأكبر (عرض 9)
                    Grid::make(1)->schema([
                        Tabs::make('معلومات المتقدم')
                            ->tabs([
                                Tab::make('بيانات شخصية')
                                    ->icon('heroicon-o-user')
                                    ->schema([
                                        TextEntry::make('FULL_NAME')->label('الاسم الكامل')->placeholder('-'),
                                        TextEntry::make('NATIONAL_NUMBER')->label('الرقم الوطني')->placeholder('-'),
                                        TextEntry::make('FIRST_NAME')->label('الاسم الأول'),
                                        TextEntry::make('LAST_NAME')->label('اللقب')->placeholder('-'),
                                        TextEntry::make('GENDER')->label('الجنس')->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(6, $state))->badge()->placeholder('-'),
                                        TextEntry::make('DATE_OF_BIRTH')->label('تاريخ الميلاد')->date()->placeholder('-'),
                                        TextEntry::make('PLACE_OF_BIRTH')->label('محل الميلاد')->placeholder('-'),
                                        TextEntry::make('PROVINCE')->label('المحافظة')->placeholder('-'),
                                        TextEntry::make('TERRITORY')->label('المديرية')->placeholder('-'),
                                        TextEntry::make('COUNTRY_NAME')->label('الدولة'),
                                        TextEntry::make('IDENT_TYPE')->label('نوع الهوية')->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(7, $state))->placeholder('-'),
                                        TextEntry::make('IDENT_NO')->label('رقم الهوية')->placeholder('-'),
                                        IconEntry::make('YEMEN_NATIONAL')->label('جنسية يمنية')->boolean(),
                                        TextEntry::make('EMAIL')->label('البريد الإلكتروني')->placeholder('-'),
                                        TextEntry::make('MOBILE_PHONE')->label('رقم الهاتف')->placeholder('-'),
                                        TextEntry::make('BLOOD_GROUP')->label('فصيلة الدم')->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(8, $state))->placeholder('-'),
                                    ])->columns(3),

                                Tab::make('بيانات الثانوية')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        TextEntry::make('SEC_SCHOOL_YEAR')->label('سنة التخرج')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(1, $state))->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_NAME')->label('اسم المدرسة')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_RATE')->label('المعدل')->placeholder('-')->suffix('%'),
                                        TextEntry::make('SEC_SCHOOL_MARK')->label('المجموع')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_OVERALLMARK')->label('المجموع الكلي')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')->placeholder('-'),
                                        TextEntry::make('SEC_SCHOOL_PLACE')->label('مكان الثانوية')->placeholder('-'),
                                    ])->columns(3),

                                Tab::make('بيانات المقاصة والقبول')
                                    ->icon('heroicon-o-document-check')
                                    ->schema([
                                        TextEntry::make('APPLICANT_TYPE')->label('نوع المتقدم')->translateFromConfig('applicant_type')->placeholder('-'),
                                        TextEntry::make('ADMITTED_ON')->label('تاريخ القبول')->date()->placeholder('-'),
                                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية المقبول بها')->placeholder('-'),
                                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص المقبول به')->placeholder('-'),
                                        TextEntry::make('ADMITTED_OFFERING')->label('رقم العرض')->placeholder('-'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn ($record) => $record?->IS_CLEARING),

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
                                        IconEntry::make('REVIEWED')->label('تمت المراجعة')->boolean(),
                                        TextEntry::make('reviewBy.USER_NAME')->label('المراجع')->placeholder('-'),
                                        TextEntry::make('REVIEW_ON')->label('تاريخ المراجعة')->dateTime()->placeholder('-'),
                                        TextEntry::make('REJECT_REASON')->label('سبب الرفض')->placeholder('-'),
                                        IconEntry::make('SECOND_REVIEWED')->label('مراجعة ثانية')->boolean(),
                                        TextEntry::make('secondReviewedBy.USER_NAME')->label('المراجع الثاني')->placeholder('-'),
                                        TextEntry::make('SECOND_REVIEWED_ON')->label('تاريخ المراجعة الثانية')->dateTime()->placeholder('-'),
                                        TextEntry::make('SECOND_REJECT_REASON')->label('سبب الرفض الثاني')->placeholder('-'),
                                    ])->columns(3),
                            ])
                            ->columnSpan('full'),
                    ])->columnSpan(9),

                    // القسم الأيسر (عرض 3)
                    Grid::make(1)->schema([
                        Section::make('معلومات أساسية')
                            ->schema([
                                TextEntry::make('STATUS')
                                    ->label('حالة الملف')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('university.U_NAME')
                                    ->label('الجامعة')
                                    ->placeholder('-'),
                                TextEntry::make('APPLICANT_IDENT')
                                    ->label('رقم التنسيق (المتقدم)')
                                    ->placeholder('-'),
                                TextEntry::make('applications_count')
                                    ->label('عدد التقديمات')
                                    ->state(fn ($record) => $record->applications()->count())
                                    ->badge()
                                    ->color('info'),
                                        TextEntry::make('IS_CLEARING')
                                            ->label('نظام المقاصة')
                                            ->translateFromConfig('is_clearing')
                                            ->placeholder('-'),
                                \Filament\Infolists\Components\IconEntry::make('FREEZE')
                                    ->label('حالة التجميد')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-minus')
                                    ->trueColor('danger')
                                    ->falseColor('gray'),
                            ]),
                    ])->columnSpan(3),
                ])->columnSpan('full'),
            ]);
    }
}
