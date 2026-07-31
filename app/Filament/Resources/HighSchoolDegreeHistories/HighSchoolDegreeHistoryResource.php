<?php

namespace App\Filament\Resources\HighSchoolDegreeHistories;

use App\Filament\Resources\HighSchoolDegreeHistories\Pages;
use App\Models\HighSchoolDegreeHistory;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;

class HighSchoolDegreeHistoryResource extends Resource
{
    protected static ?string $model = HighSchoolDegreeHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static \UnitEnum|string|null $navigationGroup = 'سجلات التاريخية';
    protected static ?string $navigationLabel = 'سجلات التعديلات';

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                  Grid::make(12)->schema([
                Section::make('تفاصيل السجل القديم للمتقدم')
                    ->schema([

                        Grid::make(3)->schema([
                            TextEntry::make('SEC_SCHOOL_YEAR')->label('السنة الدراسية'),
                            TextEntry::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس'),
                            TextEntry::make('STUDENT_NAME')->label('اسم الطالب'),
                            TextEntry::make('SEC_SCHOOL_MARK')->label('المجموع'),
                            TextEntry::make('SEC_SCHOOL_RATE')->label('المعدل'),
                            TextEntry::make('FINAL_STATUS')->label('النتيجة النهائية'),
                            TextEntry::make('SEC_SCHOOL_TYPE')->label('القسم'),
                            TextEntry::make('SEC_SCHOOL_NAME')->label('المدرسة'),
                            TextEntry::make('SEC_SCHOOL_PLACE')->label('مكان الدراسة'),
                            TextEntry::make('SEC_SCHOOL_PROVINCE')->label('المحافظة (دراسة)'),
                            TextEntry::make('SEC_SCHOOL_TERRITORY')->label('المديرية (دراسة)'),
                            TextEntry::make('GENDER')->label('الجنس'),
                            TextEntry::make('DATE_OF_BIRTH')->label('تاريخ الميلاد'),
                            TextEntry::make('PLACE_OF_BIRTH')->label('مكان الميلاد'),
                            TextEntry::make('COUNTRY_NAME')->label('الجنسية (نص)'),
                            TextEntry::make('YEMEN_NATIONAL')->label('يمني؟')->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا'),
                            TextEntry::make('NATIONALITY_NAME')->label('الجنسية المعتمدة'),
                            TextEntry::make('TERRITORY')->label('مديرية الميلاد'),
                            TextEntry::make('PROVINCE')->label('محافظة الميلاد'),
                        ]),
                    ])->columnSpan(9),
                
                Section::make('معلومات التعديل')
                ->columnSpan(3)
                    ->schema([
                        Grid::make(1)->schema([
                            TextEntry::make('UPDATE_BY')->label('تم التعديل بواسطة'),
                            TextEntry::make('UPDATE_ON')->label('تاريخ التعديل'),
                            TextEntry::make('NOTES')->label('ملاحظات'),
                        ]),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('IDENT')->label('معرف')
                    ->sortable()->searchable(),
                TextColumn::make('SEC_SCHOOL_YEAR')->label('السنة الدراسية'),
                TextColumn::make('SEC_SCHOOL_SEATNO')->label('رقم الجلوس'),
                TextColumn::make('STUDENT_NAME')->label('اسم الطالب'),
                TextColumn::make('SEC_SCHOOL_RATE')->label('المعدل'),
                TextColumn::make('GENDER')->label('الجنس'),
                TextColumn::make('YEMEN_NATIONAL')->label('يمني')->formatStateUsing(fn ($state) => $state ? 'نعم' : 'لا'),
                TextColumn::make('UPDATE_ON')->label('تاريخ التحديث')->dateTime('Y-m-d H:i'),
                TextColumn::make('UPDATE_BY')->label('المحدث بواسطة'),
                TextColumn::make('NOTES')->label('ملاحظات')->limit(50),
            ])
            ->defaultSort('UPDATE_ON', 'desc')
            ->filters([])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHighSchoolDegreeHistories::route('/'),
            'view' => Pages\ViewHighSchoolDegreeHistory::route('/{record}'),
        ];
    }
}
