<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;

class HighSchoolDegreeBTypeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        Section::make('بيانات الطالب')
                            ->columnSpan(9)
                            ->columns(3)
                            ->schema([
                                TextEntry::make('university.U_NAME')
                                    ->label('الجامعة'),
                                TextEntry::make('STUDENT_NAME')
                                    ->label('اسم الطالب'),
                                TextEntry::make('SEC_SCHOOL_SEATNO')
                                    ->label('رقم الجلوس'),
                                TextEntry::make('SEC_SCHOOL_YEAR')
                                    ->label('سنة التخرج'),
                                TextEntry::make('GENDER')
                                    ->label('الجنس'),
                                TextEntry::make('DATE_OF_BIRTH')
                                    ->label('تاريخ الميلاد')
                                    ->date(),
                                TextEntry::make('PLACE_OF_BIRTH')
                                    ->label('محل الميلاد'),
                                TextEntry::make('SEC_SCHOOL_MARK')
                                    ->label('المجموع'),
                                TextEntry::make('SEC_SCHOOL_OVERALLMARK')
                                    ->label('المجموع الكلي'),
                                TextEntry::make('SEC_SCHOOL_RATE')
                                    ->label('المعدل'),
                                TextEntry::make('SEC_SCHOOL_TYPE')
                                    ->label('نوع الثانوية'),
                                TextEntry::make('SEC_SCHOOL_NAME')
                                    ->label('اسم المدرسة'),
                                TextEntry::make('SEC_SCHOOL_PLACE')
                                    ->label('مكان المدرسة'),
                                TextEntry::make('SEC_SCHOOL_PROVINCE')
                                    ->label('محافظة الثانوية'),
                                TextEntry::make('SEC_SCHOOL_TERRITORY')
                                    ->label('مديرية الثانوية'),
                                TextEntry::make('FINAL_STATUS')
                                    ->label('الحالة النهائية'),
                            ]),

                        Group::make()
                            ->columnSpan(3)
                            ->schema([
                                Section::make('معلومات الاعتماد')
                                    ->columns(1)
                                    ->schema([
                                        IconEntry::make('APPROVED')
                                            ->label('معتمد')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle'),
                                        TextEntry::make('approvedByUser.USER_NAME')
                                            ->label('تم الاعتماد بواسطة'),
                                        TextEntry::make('APPROVED_ON')
                                            ->label('تاريخ الاعتماد')
                                            ->dateTime(),
                                        TextEntry::make('REJECT_REASON')
                                            ->label('سبب الرفض'),
                                    ]),

                                Section::make(' صورة الثانوية المرفقة')
                                    ->columns(1)
                                    ->schema([

                                        Actions::make([
                                            Action::make('view_certificate')
                                                // ->label('انقر هنا لمراجعة شهادة الثانوية')
                                                ->label(function ($record) {
                                                    if (!$record->SEC_SCHOOL_CERTIFICATE) return 'لا يوجد شهادة';
                                                    if (!auth()->user()->can('showWithCertificate', $record) && !auth()->user()->can('approve', $record)) {
                                                        return 'لا تمتلك صلاحية الاستعراض';
                                                    }
                                                    return 'انقر هنا لمراجعة شهادة الثانوية';
                                                })
                                                ->icon('heroicon-o-document-text')
                                                ->button()
                                                ->url(function ($record) {
                                                    return route('high-school.certificate.download', ['record' => $record->getKey()]);
                                                })
                                                ->openUrlInNewTab()
                                                ->color(function ($record) {
                                                    if (!$record->SEC_SCHOOL_CERTIFICATE) return 'gray';
                                                    if (!auth()->user()->can('showWithCertificate', $record) && !auth()->user()->can('approve', $record)) {
                                                        return 'gray';
                                                    }
                                                    return 'primary';
                                                })
                                                ->disabled(function ($record) {
                                                    if (!$record->SEC_SCHOOL_CERTIFICATE) return true;
                                                    if (!auth()->user()->can('showWithCertificate', $record) && !auth()->user()->can('approve', $record)) {
                                                        return true;
                                                    }
                                                    return false;
                                                })
                                        ])
                                            ->alignCenter()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                    ])->columnSpanFull(),
            ]);
    }
}
