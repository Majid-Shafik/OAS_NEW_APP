<?php

namespace App\Filament\Resources\MonitorClearingReviewings;

use App\Filament\Resources\MonitorClearingReviewings\Pages;
use App\Models\MonitorClearingReviewing;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MonitorClearingReviewingResource extends Resource
{
    protected static ?string $model = MonitorClearingReviewing::class;
    protected static ?int $navigationSort = 120;

    protected static ?string $modelLabel = 'حركة مراجعة المقاصة';

    protected static ?string $pluralModelLabel = 'مراقبة مراجعة المقاصاة';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static UnitEnum|string|null $navigationGroup = 'المقاصاة';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UNID')->label('الجامعة')->disabled(),
                TextInput::make('APPLICANT_IDENT')->label('رقم التنسيق')->disabled(),
                TextInput::make('REVIEW_RESULTE')->label('النتيجة')->disabled(),
                TextInput::make('REJECT_REASON')->label('سبب الرفض')->disabled(),
                TextInput::make('REVIEW_BY')->label('المراجع')->disabled(),
                TextInput::make('RECORD_DATE')->label('تاريخ الحركة')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('UNID')->label('الجامعة')->sortable(),
                TextColumn::make('applicant.FULL_NAME')
                    ->label('اسم المتقدم')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => \App\Filament\Resources\ClearingApplicants\ClearingApplicantResource::getUrl('view', ['record' => $record->APPLICANT_IDENT]))
                    ->openUrlInNewTab()
                    ->color('primary'),
                TextColumn::make('REVIEW_RESULTE')->label('النتيجة')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACCEPT' => 'success',
                        'REJECT' => 'danger',
                        'CANECLING' => 'warning',
                        'ReReview' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('REJECT_REASON')->label('سبب الرفض')->limit(30),
                TextColumn::make('reviewer.USER_NAME')->label('المراجع')->searchable()->sortable(),
                TextColumn::make('RECORD_DATE')->label('تاريخ الحركة')->dateTime('Y-m-d H:i:s')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitorClearingReviewings::route('/'),
            'view' => Pages\ViewMonitorClearingReviewing::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
