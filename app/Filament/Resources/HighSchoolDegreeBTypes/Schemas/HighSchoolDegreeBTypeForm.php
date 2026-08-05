<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Schemas;

use App\Helpers\PortalHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class HighSchoolDegreeBTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Grid::make(12)
                    ->schema([
                        \Filament\Schemas\Components\Group::make()
                            ->columnSpan(9)
                            ->schema([
                                Section::make('بيانات الطالب')
                                    ->columns(3)
                                    ->schema([
                                        Select::make('UNID')
                                            ->label('الجامعة')
                                            ->options(\App\Models\University::pluck('U_NAME', 'UNID'))
                                            ->searchable()
                                            ->required(),
                                        TextInput::make('STUDENT_NAME')
                                            ->label('اسم الطالب')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_SEATNO')
                                            ->label('رقم الجلوس')
                                            ->numeric(),
                                        TextInput::make('SEC_SCHOOL_YEAR')
                                            ->label('سنة التخرج')
                                            ->hint('*سنة الشهادة لعام 2012/2011 هي: 2012')
                                            ->numeric()
                                            ->required()
                                            ->live(),
                                        Select::make('GENDER')
                                            ->label('الجنس')
                                            ->options([
                                                'ذكر' => 'ذكر',
                                                'انثى' => 'أنثى',
                                            ]),
                                        DatePicker::make('DATE_OF_BIRTH')
                                            ->label('تاريخ الميلاد'),
                                        TextInput::make('PLACE_OF_BIRTH')
                                            ->label('محل الميلاد')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_MARK')
                                            ->label('المجموع')
                                            ->numeric(),
                                        TextInput::make('SEC_SCHOOL_OVERALLMARK')
                                            ->label('المجموع الكلي')
                                            ->numeric()
                                            ->default(800),
                                        TextInput::make('SEC_SCHOOL_RATE')
                                            ->label('المعدل')
                                            ->numeric(),
                                        TextInput::make('SEC_SCHOOL_TYPE')
                                            ->label('نوع الثانوية')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_NAME')
                                            ->label('اسم المدرسة')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_PLACE')
                                            ->label('مكان المدرسة')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_PROVINCE')
                                            ->label('محافظة الثانوية')
                                            ->maxLength(255),
                                        TextInput::make('SEC_SCHOOL_TERRITORY')
                                            ->label('مديرية الثانوية')
                                            ->maxLength(255),
                                        TextInput::make('FINAL_STATUS')
                                            ->label('الحالة النهائية')
                                            ->default('Pass')
                                            ->readOnly()
                                            ->maxLength(255),
                                        TextInput::make('PROVINCE')
                                            ->label('المحافظة')
                                            ->maxLength(255),
                                        TextInput::make('TERRITORY')
                                            ->label('المديرية')
                                            ->maxLength(255),
                                        Select::make('COUNTRY_IDENT')
                                            ->label('الدولة')
                                            ->relationship('country', 'COUNTRY_NAME')
                                            ->searchable(),
                                        TextInput::make('COUNTRY_NAME')
                                            ->label('اسم الدولة (نص)')
                                            ->maxLength(255),
                                        Toggle::make('YEMEN_NATIONAL')
                                            ->label('جنسية يمنية'),
                                        TextInput::make('MOBILE_PHONE')
                                            ->label('رقم الهاتف')
                                            ->maxLength(50),
                                        TextInput::make('EMAIL')
                                            ->label('البريد الإلكتروني')
                                            ->email()
                                            ->maxLength(50),
                                        FileUpload::make('SEC_SCHOOL_CERTIFICATE')
                                            ->label('صورة شهادة الثانوية')
                                            ->required()
                                            ->acceptedFileTypes(['image/jpeg', 'image/jpg'])
                                            ->disk(config('legacy_attachments.disk', 'public'))
                                            ->openable()
                                            ->imageEditor()
                                            ->downloadable()
                                            ->directory(function () {
                                                $portalPrefix = PortalHelper::getPortalPrefix();
                                                return "uploads/{$portalPrefix}/images/attachments/secondary";
                                            })
                                            ->getUploadedFileNameForStorageUsing(function () {
                                                return \Illuminate\Support\Str::random(15) . '.jpg';
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('معلومات الاعتماد')
                            ->columnSpan(3)
                            ->columns(1)
                            ->disabled()
                            ->schema([
                                Select::make('APPROVED')
                                    ->label('حالة الاعتماد')
                                    ->options([
                                        1 => 'معتمد',
                                        0 => 'غير معتمد',
                                    ]),
                                Select::make('APPROVED_BY')
                                    ->label('تم الاعتماد بواسطة')
                                    ->relationship('approvedByUser', 'USER_NAME')
                                    ->searchable(),
                                DatePicker::make('APPROVED_ON')
                                    ->label('تاريخ الاعتماد'),
                                TextInput::make('REJECT_REASON')
                                    ->label('سبب الرفض')
                                    ->maxLength(150),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
