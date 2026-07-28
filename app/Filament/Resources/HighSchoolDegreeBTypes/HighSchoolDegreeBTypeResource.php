<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes;

use App\Filament\Resources\HighSchoolDegreeBTypes\Pages\CreateHighSchoolDegreeBType;
use App\Filament\Resources\HighSchoolDegreeBTypes\Pages\EditHighSchoolDegreeBType;
use App\Filament\Resources\HighSchoolDegreeBTypes\Pages\ListHighSchoolDegreeBTypes;
use App\Filament\Resources\HighSchoolDegreeBTypes\Pages\ViewHighSchoolDegreeBType;
use App\Filament\Resources\HighSchoolDegreeBTypes\Schemas\HighSchoolDegreeBTypeForm;
use App\Filament\Resources\HighSchoolDegreeBTypes\Schemas\HighSchoolDegreeBTypeInfolist;
use App\Filament\Resources\HighSchoolDegreeBTypes\Tables\HighSchoolDegreeBTypesTable;
use App\Models\HighSchoolDegreeBType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HighSchoolDegreeBTypeResource extends Resource
{
    protected static ?string $model = HighSchoolDegreeBType::class;

    protected static ?string $modelLabel = 'شهادة الثانوية نوع B';

    protected static ?string $pluralModelLabel = 'شهادات الثانوية نوع B';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'STUDENT_NAME';

    public static function form(Schema $schema): Schema
    {
        return HighSchoolDegreeBTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HighSchoolDegreeBTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HighSchoolDegreeBTypesTable::configure($table);
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
            'index' => ListHighSchoolDegreeBTypes::route('/'),
            'create' => CreateHighSchoolDegreeBType::route('/create'),
            'view' => ViewHighSchoolDegreeBType::route('/{record}'),
            'edit' => EditHighSchoolDegreeBType::route('/{record}/edit'),
        ];
    }

    public static function getReviewAction(string $actionClass)
    {
        return $actionClass::make('review')
            ->label('مراجعة')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn ($record) => $record && $record->APPROVED !== 1)
            ->authorize(fn () => auth()->user()->can('Approve:HighSchoolDegreeBType'))
            ->form([
                \Filament\Forms\Components\Select::make('APPROVED')
                    ->label('نتيجة المراجعة')
                    ->options([
                        1 => 'اعتماد',
                        0 => 'رفض',
                    ])
                    ->required()
                    ->live(),
                \Filament\Forms\Components\TextInput::make('REJECT_REASON')
                    ->label('سبب الرفض')
                    ->visible(fn (Get $get) => $get('APPROVED') === 0)
                    ->required(fn (Get $get) => $get('APPROVED') === 0),
            ])
            ->action(function ($record, array $data) {
                $record->update([
                    'APPROVED' => $data['APPROVED'],
                    'REJECT_REASON' => $data['APPROVED'] == 1 ? null : $data['REJECT_REASON'],
                    'APPROVED_BY' => auth()->id(),
                    'APPROVED_ON' => now(),
                ]);
                \Filament\Notifications\Notification::make()->success()->title('تم حفظ المراجعة بنجاح')->send();
            });
    }
}
