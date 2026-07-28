<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Tables;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HighSchoolDegreeBTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('SS_IDENT')
                    ->label('الرقم')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('STUDENT_NAME')
                    ->label('اسم الطالب')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_SEATNO')
                    ->label('رقم الجلوس')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_YEAR')
                    ->label('سنة التخرج')
                    ->sortable(),
                TextColumn::make('SEC_SCHOOL_RATE')
                    ->label('المعدل')
                    ->sortable(),
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->sortable(),
                IconColumn::make('APPROVED')
                    ->label('معتمد')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                TextColumn::make('RECORDDATE')
                    ->label('تاريخ التسجيل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('APPROVED')
                    ->label('حالة الاعتماد')
                    ->trueLabel('معتمد')
                    ->falseLabel('غير معتمد')
                    ->placeholder('الكل')
                    ->queries(
                        true: fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('APPROVED', 1),
                        false: fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where(fn($q) => $q->whereNull('APPROVED')->orWhere('APPROVED', 0)),
                        blank: fn(\Illuminate\Database\Eloquent\Builder $query) => $query,
                    ),
                \Filament\Tables\Filters\SelectFilter::make('UNID')
                    ->label('الجامعة')
                    ->relationship('university', 'U_NAME')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('SEC_SCHOOL_YEAR')
                    ->label('عام الثانوية')
                    ->options(
                        fn() => \App\Models\HighSchoolDegreeBType::query()
                            ->select('SEC_SCHOOL_YEAR')
                            ->whereNotNull('SEC_SCHOOL_YEAR')
                            ->distinct()
                            ->orderByDesc('SEC_SCHOOL_YEAR')
                            ->pluck('SEC_SCHOOL_YEAR', 'SEC_SCHOOL_YEAR')
                            ->toArray()
                    ),
                \Filament\Tables\Filters\SelectFilter::make('PROVINCE')
                    ->label('المحافظة')
                    ->options(
                        fn() => \App\Models\HighSchoolDegreeBType::query()
                            ->select('PROVINCE')
                            ->whereNotNull('PROVINCE')
                            ->where('PROVINCE', '!=', '')
                            ->distinct()
                            ->pluck('PROVINCE', 'PROVINCE')
                            ->toArray()
                    )
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('GENDER')
                    ->label('الجنس')
                    ->options([
                        'ذكر' => 'ذكر',
                        'انثى' => 'أنثى',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('YEMEN_NATIONAL')
                    ->label('الجنسية')
                    ->trueLabel('يمني')
                    ->falseLabel('غير يمني')
                    ->placeholder('الكل'),
            ])
            ->recordActions([
                \App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource::getReviewAction(Action::class),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
