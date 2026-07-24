<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use App\Models\UserGroup;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(12)
                    ->schema([
                        Section::make('المعلومات الأساسية')
                            ->schema([
                                TextEntry::make('UNID')
                                    ->label('الجامعة')
                                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : University::find($state)?->U_NAME ?? $state),
                                TextEntry::make('FACULTY_IDENT')
                                    ->label('الكلية')
                                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : Faculty::find($state)?->FACULTY_NAME ?? $state),
                                TextEntry::make('PROGRAM_IDENT')
                                    ->label('التخصص')
                                    ->formatStateUsing(fn($state) => $state == 0 ? 'غير محدد' : Program::find($state)?->PROGRAM_NAME ?? $state),
                                TextEntry::make('GROUP_IDENT')
                                    ->label('المجموعة (الدور القديم)')
                                    ->formatStateUsing(fn($state) => UserGroup::find($state)?->GROUP_NAME ?? $state),
                                TextEntry::make('roles.label')
                                    ->label('الدور الوظيفي')
                                    ->badge(),
                                TextEntry::make('USER_NAME')
                                    ->label('اسم المستخدم'),
                                TextEntry::make('LOGON_ID')
                                    ->label('اسم تسجيل الدخول'),
                                TextEntry::make('EMAIL')
                                    ->label('البريد الإلكتروني'),
                                TextEntry::make('MOBILE_PHONE')
                                    ->label('رقم الجوال'),
                                TextEntry::make('IDENT_TYPE')
                                    ->label('نوع الهوية')
                                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(7, $state)),
                                TextEntry::make('IDENT_NO')
                                    ->label('رقم الهوية'),
                                TextEntry::make('GENDER')
                                    ->label('الجنس')
                                    ->formatStateUsing(fn($state) => \App\Models\ComboValue::getLabel(6, $state)),
                                IconEntry::make('FIRST_TIME')
                                    ->label('دخول لأول مرة')
                                    ->boolean(),
                                TextEntry::make('RECORDDATE')
                                    ->label('تاريخ التسجيل')
                                    ->dateTime(),
                                TextEntry::make('inserter.USER_NAME')
                                    ->label('أضيف بواسطة'),
                            ])
                            ->columns(3)
                            ->columnSpan(9),

                        Section::make('الحساب')
                            ->schema([
                                ImageEntry::make('profile_photo_fake')
                                    ->label('صورة المستخدم')
                                    ->circular()
                                    ->defaultImageUrl('https://ui-avatars.com/api/?name=User&color=7F9CF5&background=EBF4FF'),
                                IconEntry::make('IS_IT_ENABLE')
                                    ->label('حالة الحساب')
                                    ->boolean(),
                            ])
                            ->columnSpan(3)
                            ->extraAttributes([
                                'class' => 'sticky top-6',
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
