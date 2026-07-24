<?php

namespace App\Filament\Resources\DeletedApplications;

use App\Filament\Resources\Applications\Schemas\ApplicationInfolist;
use App\Filament\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use App\Models\DeletedApplication;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DeletedApplicationResource extends Resource
{
    protected static ?string $model = DeletedApplication::class;

    protected static ?string $modelLabel = 'طلب محذوف';

    protected static ?string $pluralModelLabel = 'الطلبات المحذوفة';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static UnitEnum|string|null $navigationGroup = 'إدارة المتقدمين';



    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        // Re-use applications table columns and just replace the actions
        $baseTable = ApplicationsTable::configure($table);
        
        $columns = $baseTable->getColumns();
        // Add deleted info
        $columns[] = TextColumn::make('deletedBy.USER_NAME')->label('محذوف بواسطة')->sortable()->toggleable();
        $columns[] = TextColumn::make('deleted_at')->label('تاريخ الحذف')->dateTime()->sortable();
        
        return $table
            ->columns($columns)
            ->filters($baseTable->getFilters())
            ->actions([
                Action::make('view')
                    ->label('استعراض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
                Action::make('restore')
                    ->label('استرجاع')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('استرجاع طلب التقديم')
                    ->modalDescription('هل أنت متأكد من استرجاع هذا الطلب؟ تأكد من عدم وجود طلب تقديم فعال حالياً لنفس التخصص للمتقدم.')
                    ->action(function (DeletedApplication $record) {
                        // Check for duplication before restore
                        $exists = Application::where('APPLICANT_IDENT', $record->APPLICANT_IDENT)
                            ->where('PROGRAM_IDENT', $record->PROGRAM_IDENT)
                            ->where('STUDYTYPE_IDENT', $record->STUDYTYPE_IDENT)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->danger()
                                ->title('فشل الاسترجاع')
                                ->body('عذراً، يوجد بالفعل طلب تقديم فعال لهذا المتقدم في نفس التخصص والنظام الدراسي.')
                                ->send();
                            return;
                        }

                        // Restore logic
                        $data = $record->toArray();
                        unset($data['deleted_at']);
                        unset($data['deleted_by']);
                        
                        Application::insert($data);
                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('تم الاسترجاع')
                            ->body('تم استرجاع طلب التقديم بنجاح.')
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->can('restore', new DeletedApplication())),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return ApplicationInfolist::configure($infolist);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDeletedApplications::route('/'),
            'view' => Pages\ViewDeletedApplication::route('/{record}'),
        ];
    }
}
