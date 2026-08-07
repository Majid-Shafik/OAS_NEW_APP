<?php

namespace App\Filament\Resources\RequestAdjustOfferings;

use App\Enums\RequestUpdateType;
use App\Filament\Resources\RequestAdjustOfferings\Pages\ManageRequestAdjustOfferings;
use App\Models\RequestAdjustOffering;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RequestAdjustOfferingResource extends Resource
{
    protected static ?string $model = RequestAdjustOffering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'طلب تعديل معيار';

    protected static ?string $pluralModelLabel = 'طلبات تعديل المعايير';

    protected static \UnitEnum|string|null $navigationGroup = 'المعايير';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('ACCEPT')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::whereNull('ACCEPT')->count() > 0 ? 'warning' : 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('UNID')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('FACULTY_IDENT')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('PROGRAM_IDENT')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('OFFERING_IDENT')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('TYPE_UPDATE')
                    ->required()
                    ->default(0),
                TextInput::make('NEW_ACCEPT_RATE')
                    ->numeric()
                    ->default(0),
                DatePicker::make('FROM_DATE'),
                DateTimePicker::make('TO_DATE'),
                TextInput::make('NEW_Y_SEC_SCHOOL_MAX_AGE')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('NOTE')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('ADD_BY')
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('RECORDED_ON'),
                Select::make('ACCEPT')
                    ->label('القرار (مقبول)')
                    ->options([
                        '1' => 'موافق وتم التنفيذ',
                        '0' => 'مرفوض',
                    ])
                    ->live(),
                Textarea::make('REASON')
                    ->label('سبب الرفض')
                    ->visible(fn (Get $get) => $get('ACCEPT') == '0')
                    ->required(fn (Get $get) => $get('ACCEPT') == '0')
                    ->columnSpanFull(),
                Toggle::make('RUN_IT'),
                DateTimePicker::make('RUN_ON'),
                TextInput::make('RUN_BY')
                    ->numeric()
                    ->default(0),
                FileUpload::make('un_attachment')
                    ->label('مرفق الجامعة (وثيقة الطلب)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('university.U_NAME')
                    ->label('الجامعة')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('offering.studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('OFFERING_IDENT')
                    ->label('رقم الرغبة')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('TYPE_UPDATE')
                    ->label('نوع التعديل')
                    ->badge()
                    ->sortable(),
                TextColumn::make('NEW_ACCEPT_RATE')
                    ->label('معدل القبول الجديد')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('FROM_DATE')
                    ->label('من تاريخ')
                    ->date()
                    ->sortable(),
                TextColumn::make('TO_DATE')
                    ->label('إلى تاريخ')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('NEW_Y_SEC_SCHOOL_MAX_AGE')
                    ->label('عمر الثانوية الجديد')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('un_attachment_virtual')
                    ->label('مرفق الجامعة')
                    ->getStateUsing(fn ($record) => file_exists($record->getUnAttachmentPath()) ? 'عرض المرفق' : 'لا يوجد')
                    ->url(fn ($record) => file_exists($record->getUnAttachmentPath()) ? $record->getUnAttachmentUrl() : null)
                    ->color('primary')
                    ->icon('heroicon-o-document-text')
                    ->openUrlInNewTab(),
                TextColumn::make('ministry_attachment_virtual')
                    ->label('مرفق الوزارة')
                    ->getStateUsing(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? 'عرض المرفق' : 'لا يوجد')
                    ->url(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? $record->getMinistryAttachmentUrl() : null)
                    ->color('success')
                    ->icon('heroicon-o-document-check')
                    ->openUrlInNewTab(),
                TextColumn::make('addedBy.USER_NAME')
                    ->label('أضيف بواسطة')
                    ->sortable(),
                TextColumn::make('RECORDED_ON')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('ACCEPT')
                    ->label('القرار (مقبول)')
                    ->boolean(),
                IconColumn::make('RUN_IT')
                    ->label('تم التنفيذ')
                    ->boolean(),
                TextColumn::make('RUN_ON')
                    ->label('تاريخ التنفيذ')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('runBy.USER_NAME')
                    ->label('نُفذ بواسطة')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('TYPE_UPDATE')
                    ->label('نوع التعديل')
                    ->options(RequestUpdateType::class),
                SelectFilter::make('ACCEPT')
                    ->label('حالة الطلب (القبول)')
                    ->options([
                        '1' => 'مقبول وتم التنفيذ',
                        '0' => 'مرفوض',
                    ]),
                Filter::make('pending')
                    ->label('طلبات قيد المراجعة')
                    ->query(fn (Builder $query): Builder => $query->whereNull('ACCEPT'))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('review')
                    ->label('مراجعة الطلب (للوزارة)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (RequestAdjustOffering $record) => is_null($record->ACCEPT))
                    ->form([
                        Select::make('ACCEPT')
                            ->label('القرار')
                            ->options([
                                '1' => 'موافق وتم التنفيذ',
                                '0' => 'مرفوض',
                            ])
                            ->required()
                            ->live(),

                        Textarea::make('REASON')
                            ->label('سبب الرفض')
                            ->visible(fn (Get $get) => $get('ACCEPT') == '0')
                            ->required(fn (Get $get) => $get('ACCEPT') == '0'),

                        FileUpload::make('ministry_attachment')
                            ->label('مرفق الوزارة (وثيقة الاعتماد أو الرفض)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ])
                    ->action(function (array $data, RequestAdjustOffering $record) {
                        $file = $data['ministry_attachment'] ?? null;
                        if ($file) {
                            $filePath = is_array($file) ? array_values($file)[0] : $file;
                            $disk = Storage::disk(config('legacy_attachments.disk', 'public'));
                            if ($disk->exists($filePath)) {
                                $dbName = DB::connection()->getDatabaseName();
                                $basePath = config("legacy_attachments.systems.{$dbName}", "uploads/{$dbName}").'/uploads_pdf/ministry';
                                $newPath = $basePath.'/req_'.$record->REQUEST_ID.'.pdf';

                                if ($disk->exists($newPath)) {
                                    $disk->delete($newPath);
                                }

                                $disk->move($filePath, $newPath);
                            }
                        }

                        $record->ACCEPT = $data['ACCEPT'];
                        $record->REASON = $data['REASON'] ?? null;

                        if ($data['ACCEPT'] == '1') {
                            $offering = $record->offering;

                            if ($record->TYPE_UPDATE == RequestUpdateType::ACCEPT_RATE) {
                                $offering->update(['SEC_SCHOOL_ACCEPT_RATE' => $record->NEW_ACCEPT_RATE]);
                            } elseif ($record->TYPE_UPDATE == RequestUpdateType::COORDINATION_PERIOD) {
                                $offering->update([
                                    'FROM_DATE' => $record->FROM_DATE,
                                    'TO_DATE' => $record->TO_DATE,
                                ]);
                            } elseif ($record->TYPE_UPDATE == RequestUpdateType::PAYMENT_PERIOD) {
                                if (strtotime($record->TO_DATE) > strtotime($offering->TO_DATE)) {
                                    DB::table('offerings_groups')
                                        ->where('OFFER_GROUP_IDENT', $offering->OFFER_GROUP_IDENT)
                                        ->update(['FINISHED_PAYMENT_DATE' => $record->TO_DATE]);
                                } else {
                                    Notification::make()
                                        ->title('فشل التمديد')
                                        ->body('لا يمكن تمديد التسديد، حيث التنسيق لا يزال مستمرا إلى ما بعد تمديد التسديد.')
                                        ->danger()
                                        ->send();

                                    return; // Abort approval
                                }
                            } elseif ($record->TYPE_UPDATE == RequestUpdateType::SEC_SCHOOL_AGE) {
                                $offering->update(['Y_SEC_SCHOOL_MAX_AGE' => $record->NEW_Y_SEC_SCHOOL_MAX_AGE]);
                            }

                            $record->RUN_IT = 1;
                            $record->RUN_BY = auth()->id() ?? 1;
                            $record->RUN_ON = now();
                        }

                        $record->save();

                        Notification::make()
                            ->title('تم حفظ القرار وتحديث البيانات بنجاح')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('معلومات الطلب الأساسية')
                    ->schema([
                        TextEntry::make('university.U_NAME')->label('الجامعة')->placeholder('-'),
                        TextEntry::make('faculty.FACULTY_NAME')->label('الكلية')->placeholder('-'),
                        TextEntry::make('program.PROGRAM_NAME')->label('التخصص')->placeholder('-'),
                        TextEntry::make('offering.studyType.STUDYTYPE_NAME')->label('النظام الدراسي')->placeholder('-'),
                        TextEntry::make('OFFERING_IDENT')->label('رقم الرغبة'),
                        TextEntry::make('TYPE_UPDATE')
                            ->label('نوع التعديل')
                            ->badge(),
                    ])->columns(6)
                    ->columnSpanFull(),

                Section::make('تفاصيل التعديل')
                    ->schema([
                        TextEntry::make('NEW_ACCEPT_RATE')->label('معدل القبول الجديد')->suffix('%')->placeholder('-'),
                        TextEntry::make('FROM_DATE')->label('من تاريخ')->date()->placeholder('-'),
                        TextEntry::make('TO_DATE')->label('إلى تاريخ')->dateTime()->placeholder('-'),
                        TextEntry::make('NEW_Y_SEC_SCHOOL_MAX_AGE')->label('عمر الثانوية الجديد')->placeholder('-'),
                    ])->columns(4)->columnSpanFull(),

                Section::make('سبب طلب التعديل ')
                    ->schema([
                        TextEntry::make('NOTE')->label('ملاحظات الطلب')->columnSpan(3)->placeholder('-'),
                        TextEntry::make('addedBy.USER_NAME')->label('أضيف بواسطة')->placeholder('-'),
                        TextEntry::make('RECORDED_ON')->label('تاريخ الإضافة')->dateTime()->placeholder('-'),
                    ])->columns(5)->columnSpanFull(),

                Section::make('القرار والتنفيذ')
                    ->schema([
                        IconEntry::make('ACCEPT')->label('القرار (مقبول)')->boolean(),
                        TextEntry::make('REASON')->columnSpan(2)->placeholder('-')
                            ->label('سبب الرفض')
                            ->placeholder('-')
                            ->visible(fn ($record) => $record?->ACCEPT == '0'),
                        IconEntry::make('RUN_IT')->label('تم التنفيذ')->boolean(),
                        TextEntry::make('RUN_ON')->label('تاريخ التنفيذ')->dateTime()->placeholder('-'),
                        TextEntry::make('runBy.USER_NAME')->label('نُفذ بواسطة')->placeholder('-'),
                    ])->columns(6)->columnSpanFull(),

                Section::make('المرفقات')
                    ->schema([
                        TextEntry::make('un_attachment_virtual')
                            ->label('مرفق الجامعة')
                            ->getStateUsing(fn ($record) => file_exists($record->getUnAttachmentPath()) ? 'عرض المرفق' : 'لا يوجد')
                            ->url(fn ($record) => file_exists($record->getUnAttachmentPath()) ? $record->getUnAttachmentUrl() : null)
                            ->color(fn ($record) => file_exists($record->getUnAttachmentPath()) ? 'primary' : 'gray')
                            ->icon(fn ($record) => file_exists($record->getUnAttachmentPath()) ? 'heroicon-o-document-text' : 'heroicon-o-x-circle')
                            ->openUrlInNewTab(),
                        TextEntry::make('ministry_attachment_virtual')
                            ->label('مرفق الوزارة')
                            ->getStateUsing(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? 'عرض المرفق' : 'لا يوجد')
                            ->url(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? $record->getMinistryAttachmentUrl() : null)
                            ->color(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? 'success' : 'gray')
                            ->icon(fn ($record) => file_exists($record->getMinistryAttachmentPath()) ? 'heroicon-o-document-check' : 'heroicon-o-x-circle')
                            ->openUrlInNewTab(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRequestAdjustOfferings::route('/'),
        ];
    }
}
