<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(12)->schema([
                    // القسم الأيمن: بيانات أكاديمية ومالية (8 أعمدة)
                    Group::make()->schema([
                        Section::make('البيانات الأكاديمية')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('APPLICATION_IDENT')->label('رقم التقديم'),
                                TextEntry::make('university.U_NAME')->label('الجامعة'),
                                TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                                TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                                TextEntry::make('studyType.STUDYTYPE_NAME')->label('النظام الدراسي'),
                                TextEntry::make('CHOICE_NO')->label('رقم الرغبة'),
                                TextEntry::make('SEC_SCHOOL_RATE')->label('معدل الثانوية'),
                                TextEntry::make('ENTRANCE_EXAM_AVERAGE')->label('معدل امتحان القبول'),
                                TextEntry::make('ENTRANCE_EXAM_WEIGHT')->label('وزن امتحان القبول'),
                                TextEntry::make('FINAL_MARK')->label('الدرجة النهائية (المفاضلة)'),
                                TextEntry::make('OFFERING_IDENT')->label('رقم العرض'),
                                TextEntry::make('OFFER_GROUP_IDENT')->label('مجموعة العرض'),
                            ]),

                        Section::make('البيانات المالية وحالة القبول')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('STATUS')->label('الحالة')->badge(),
                                IconEntry::make('ACCEPTED')->label('مقبول')->boolean(),
                                IconEntry::make('CONFIRMED_BY_APPLICANT')->label('مؤكد')->boolean(),
                                TextEntry::make('APP_BILL_IDENT')->label('رقم السند'),
                                TextEntry::make('PAYMENT_FLAG')->label('طريقة الدفع')->badge(),
                                TextEntry::make('RECORDDATE')->label('تاريخ التسديد / الإضافة')->dateTime(),
                                TextEntry::make('insertedBy.USER_NAME')->label('تمت الإضافة / التسديد بواسطة'),
                                TextEntry::make('CONFIRMED_ON')->label('تاريخ التأكيد')->dateTime(),
                            ]),
                    ])->columnSpan(['default' => 12, 'md' => 9]),

                    // القسم الأيسر: بيانات المتقدم (4 أعمدة)
                    Group::make()->schema([
                        Section::make('بيانات المتقدم')
                            ->schema([
                                TextEntry::make('applicant.FULL_NAME')
                                    ->label('اسم المتقدم')
                                    ->url(fn($record) => $record->applicant ? ApplicantResource::getUrl('view', ['record' => $record->applicant]) : null)
                                    ->tooltip('انقر هنا للانتقال إلى ملف المتقدم')
                                    ->openUrlInNewTab()
                                    ->color('primary')
                                    ->extraAttributes(['class' => 'underline font-bold']),
                                TextEntry::make('applicant.APPLICANT_IDENT')->label('رقم التنسيق للمتقدم'),
                                TextEntry::make('STUDENT_CODE')->label('رقم الطالب الجامعي'),
                                IconEntry::make('SHAW_APPLICANT_RESULTE')->label('إظهار النتيجة للمتقدم')->boolean(),
                                IconEntry::make('EXPORTED')->label('مُصدَّر (EXPORTED)')->boolean(),
                            ]),
                    ])->columnSpan(['default' => 12, 'md' => 3]),
                ])->columnSpanFull(),
            ]);
    }
}
