<?php

namespace App\Filament\Resources\OfferingDhs;

use App\Filament\Resources\OfferingDhs\Pages\ManageOfferingDhs;
use App\Models\OfferingDh;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static ?string $recordTitleAttribute = 'REVESION';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('REVESION')
                    ->label('المراجعة (رقم)')
                    ->sortable(),
                TextColumn::make('ACTION')
                    ->label('الإجراء')
                    ->searchable()
                    ->badge(),
                TextColumn::make('user.USER_NAME')
                    ->label('المستخدم')
                    ->searchable(),
                TextColumn::make('ATIME')
                    ->label('وقت التعديل')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->searchable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النوع الدراسي')
                    ->searchable(),
            ])
            ->filters([
                \App\Filament\Filters\AcademicFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
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
                        TextEntry::make('SEC_SCHOOL_ACCEPT_RATE')->label('معدل القبول'),
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
