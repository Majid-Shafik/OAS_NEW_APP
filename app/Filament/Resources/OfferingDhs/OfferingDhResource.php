<?php

namespace App\Filament\Resources\OfferingDhs;

use App\Filament\Filters\AcademicFilter;
use App\Filament\Resources\OfferingDhs\Pages\ManageOfferingDhs;
use App\Models\OfferingDh;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class OfferingDhResource extends Resource
{
    protected static ?string $model = OfferingDh::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static UnitEnum|string|null $navigationGroup = 'المعايير';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'مراقبة تعديل معيار';

    protected static ?string $pluralModelLabel = 'مراقبة تعديلات المعايير';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    protected static ?string $recordTitleAttribute = 'REVESION';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getSharedTableColumns(bool $isRelation = false): array
    {
        $columns = [
            TextColumn::make('REVESION')->label('رقم المراجعة')->sortable(),
            TextColumn::make('ACTION')->label('نوع الإجراء')->badge(),
            TextColumn::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية'),
            TextColumn::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول (الجديد)')->numeric()->suffix('%'),
            TextColumn::make('ENTRANCE_EXAM_WEIGHT')->label('وزن الامتحان (الجديد)')->numeric(),
            TextColumn::make('Y_SEC_SCHOOL_MAX_AGE')->label('عمر الثانوي (يمني)')->numeric(),
            TextColumn::make('NY_SEC_SCHOOL_MAX_AGE')->label('عمر الثانوي (غير يمني)')->numeric(),
            IconColumn::make('ENTRANCE_EXAM_REQUIRED')->label('امتحان مطلوب')->boolean(),
            TextColumn::make('FROM_DATE')->label('من تاريخ')->date(),
            TextColumn::make('TO_DATE')->label('إلى تاريخ')->date(),
            TextColumn::make('lastUpdatedBy.USER_NAME')->label('تم التعديل بواسطة'),
            TextColumn::make('ATIME')->label('تاريخ التعديل')->dateTime(),
            TextColumn::make('approvalBy.USER_NAME')->label('الاعتماد بواسطة'),
            TextColumn::make('APPROVAL_ON')->label('تاريخ الاعتماد')->dateTime(),
            IconColumn::make('APPROVAL')->label('حالة الاعتماد')->boolean(),
            TextColumn::make('APPROVAL_REGECT_REASON')->label('سبب الرفض'),
        ];

        if (! $isRelation) {
            array_unshift($columns, TextColumn::make('OFFERING_IDENT')->label('رقم المعيار')->sortable());
            array_push(
                $columns,
                TextColumn::make('university.U_NAME')->label('الجامعة')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('faculty.FACULTY_NAME')->label('الكلية')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('program.PROGRAM_NAME')->label('التخصص')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('studyType.STUDYTYPE_NAME')->label('النوع الدراسي')->searchable()->toggleable(isToggledHiddenByDefault: true)
            );
        }

        return $columns;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getSharedTableColumns(false))
            ->filters([
                AcademicFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                // Read-only
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات التعديل')
                    ->schema([
                        TextEntry::make('REVESION')->label('المراجعة'),
                        TextEntry::make('ACTION')->label('نوع الإجراء')->badge(),
                        TextEntry::make('user.USER_NAME')->label('المستخدم المسؤول'),
                        TextEntry::make('ATIME')->label('وقت التعديل')->dateTime(),
                    ])->columns(4),

                Section::make('بيانات المعيار (وقت التعديل)')
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة'),
                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص'),
                        TextEntry::make('studyType.STUDYTYPE_NAME')->label('النوع الدراسي'),
                        TextEntry::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية'),
                        TextEntry::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول')->suffix('%'),
                        TextEntry::make('ENTRANCE_EXAM_WEIGHT')->label('وزن امتحان القبول'),
                        TextEntry::make('STUDY_FEES')->label('الرسوم الدراسية'),
                        IconEntry::make('ENTRANCE_EXAM_REQUIRED')->label('امتحان القبول مطلوب؟')->boolean(),
                        TextEntry::make('FROM_DATE')->label('من تاريخ')->date(),
                        TextEntry::make('TO_DATE')->label('إلى تاريخ')->date(),
                        TextEntry::make('recordedBy.USER_NAME')->label('مضاف بواسطة'),
                        TextEntry::make('approvalBy.USER_NAME')->label('معتمد بواسطة'),
                    ])->columns(3),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOfferingDhs::route('/'),
        ];
    }
}
