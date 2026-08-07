<?php

namespace App\Filament\Resources\Offerings\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfferingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات المعيار الأساسية')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة'),
                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                        TextEntry::make('studyType.STUDYTYPE_NAME')->label('النظام الدراسي'),
                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')
                            ->formatStateUsing(fn ($state) => \App\Models\ComboValue::getLabel(1, $state)),
                    ])->columns(3),
                Section::make('معلومات مجموعة التنسيق')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('offeringGroup.DESCRIPTION')->label('وصف المجموعة'),
                        TextEntry::make('offeringGroup.MIN_CHOICE')->label('الحد الأدنى للرغبات'),
                        TextEntry::make('offeringGroup.MAX_CHOICE')->label('الحد الأعلى للرغبات'),
                        TextEntry::make('offeringGroup.APPLYING_COST')->label('رسوم التنسيق'),
                        IconEntry::make('offeringGroup.ENABLE_PAYMENT')->label('تفعيل الدفع')->boolean(),
                        TextEntry::make('offeringGroup.STARTED_PAYMENT_DATE')
                            ->label('تاريخ بداية السداد')
                            ->date()
                            ->visible(fn (\Illuminate\Database\Eloquent\Model $record) => $record->offeringGroup?->ENABLE_PAYMENT),
                        TextEntry::make('offeringGroup.FINISHED_PAYMENT_DATE')
                            ->label('تاريخ نهاية السداد')
                            ->dateTime()
                            ->visible(fn (\Illuminate\Database\Eloquent\Model $record) => $record->offeringGroup?->ENABLE_PAYMENT),
                    ])->columns(4),
                Section::make('تفضيلات وإعدادات القبول')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول')->suffix('%'),
                        TextEntry::make('ENTRANCE_EXAM_WEIGHT')->label('وزن امتحان القبول'),
                        TextEntry::make('Y_SEC_SCHOOL_MAX_AGE')->label('أقصى عمر يمني'),
                        TextEntry::make('NY_SEC_SCHOOL_MAX_AGE')->label('أقصى عمر غير يمني'),
                        TextEntry::make('STUDY_FEES')->label('الرسوم الدراسية'),
                        TextEntry::make('STUDY_FEES_NY')->label('الرسوم لغير اليمني'),
                        IconEntry::make('ENTRANCE_EXAM_REQUIRED')->label('امتحان القبول مطلوب؟')->boolean(),
                        TextEntry::make('FROM_DATE')->label('من تاريخ التنسيق')->date(),
                        TextEntry::make('TO_DATE')->label('إلى تاريخ التنسيق')->date(),
                    ])->columns(3),
                Section::make('معلومات التسجيل والمراجعة')
                    ->collapsible()
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('recordedBy.USER_NAME')->label('بواسطة (إضافة)'),
                        TextEntry::make('RECORD_ON')->label('تاريخ الإضافة')->dateTime(),
                        TextEntry::make('lastUpdatedBy.USER_NAME')->label('بواسطة (تحديث)'),
                        TextEntry::make('LAST_UPDATED_ON')->label('تاريخ التحديث')->dateTime(),
                        TextEntry::make('approvalBy.USER_NAME')->label('تم الاعتماد بواسطة'),
                        TextEntry::make('APPROVAL_ON')->label('تاريخ الاعتماد')->dateTime(),
                        IconEntry::make('APPROVAL')->label('حالة الاعتماد')->boolean(),
                        TextEntry::make('APPROVAL_REGECT_REASON')->label('سبب الرفض'),
                    ])->columns(3),
            ]);
    }
}
