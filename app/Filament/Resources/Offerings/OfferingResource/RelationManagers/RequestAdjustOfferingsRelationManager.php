<?php

namespace App\Filament\Resources\Offerings\OfferingResource\RelationManagers;

use App\Enums\RequestUpdateType;
use App\Models\RequestAdjustOffering;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class RequestAdjustOfferingsRelationManager extends RelationManager
{
    protected static string $relationship = 'requestAdjustOfferings';

    protected static ?string $title = 'طلبات تعديل المعيار';

    protected static ?string $modelLabel = 'طلب تعديل';

    protected static ?string $pluralModelLabel = 'طلبات تعديل المعيار';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('TYPE_UPDATE')
                    ->label('نوع التعديل')
                    ->options(RequestUpdateType::class)
                    ->required()
                    ->live(), // لكي يتم تحديث الحقول الأخرى بناءً على الاختيار

                Forms\Components\TextInput::make('NEW_ACCEPT_RATE')
                    ->label('معدل القبول الجديد')
                    ->numeric()
                    ->suffix('%')
                    ->visible(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::ACCEPT_RATE->value)
                    ->required(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::ACCEPT_RATE->value),

                Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('FROM_DATE')
                            ->label('من تاريخ (بداية التنسيق)')
                            ->visible(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::COORDINATION_PERIOD->value)
                            ->required(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::COORDINATION_PERIOD->value),
                        Forms\Components\DatePicker::make('TO_DATE')
                            ->label(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::PAYMENT_PERIOD->value ? 'إلى تاريخ (نهاية فترة التسديد)' : 'إلى تاريخ (نهاية التنسيق)')
                            ->visible(fn (Get $get) => in_array($get('TYPE_UPDATE'), [RequestUpdateType::COORDINATION_PERIOD->value, RequestUpdateType::PAYMENT_PERIOD->value]))
                            ->required(fn (Get $get) => in_array($get('TYPE_UPDATE'), [RequestUpdateType::COORDINATION_PERIOD->value, RequestUpdateType::PAYMENT_PERIOD->value])),
                    ]),

                Forms\Components\TextInput::make('NEW_Y_SEC_SCHOOL_MAX_AGE')
                    ->label('عمر الثانوية الجديد')
                    ->numeric()
                    ->visible(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::SEC_SCHOOL_AGE->value)
                    ->required(fn (Get $get) => $get('TYPE_UPDATE') == RequestUpdateType::SEC_SCHOOL_AGE->value),

                Forms\Components\Textarea::make('NOTE')
                    ->label('سبب طلب التعديل (ملاحظات)')
                    ->minLength(10)
                    ->columnSpanFull()
                    ->required(),

                Forms\Components\FileUpload::make('un_attachment')
                    ->label('مرفق الجامعة (وثيقة الطلب)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('REQUEST_ID')
            ->columns([
                Tables\Columns\TextColumn::make('REQUEST_ID')->label('الرقم')->sortable(),
                Tables\Columns\TextColumn::make('TYPE_UPDATE')
                    ->label('نوع التعديل')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof RequestUpdateType ? $state->getLabel() : $state),
                Tables\Columns\TextColumn::make('un_attachment_virtual')
                    ->label('مرفق الجامعة')
                    ->getStateUsing(fn ($record) => file_exists(public_path('uploads_pdf/un/req_'.$record->REQUEST_ID.'.pdf')) ? 'عرض المرفق' : 'لا يوجد')
                    ->url(fn ($record) => file_exists(public_path('uploads_pdf/un/req_'.$record->REQUEST_ID.'.pdf')) ? asset('uploads_pdf/un/req_'.$record->REQUEST_ID.'.pdf') : null)
                    ->color('primary')
                    ->icon('heroicon-o-document-text'),
                Tables\Columns\TextColumn::make('ministry_attachment_virtual')
                    ->label('مرفق الوزارة')
                    ->getStateUsing(fn ($record) => file_exists(public_path('uploads_pdf/ministry/req_'.$record->REQUEST_ID.'.pdf')) ? 'عرض المرفق' : 'لا يوجد')
                    ->url(fn ($record) => file_exists(public_path('uploads_pdf/ministry/req_'.$record->REQUEST_ID.'.pdf')) ? asset('uploads_pdf/ministry/req_'.$record->REQUEST_ID.'.pdf') : null)
                    ->color('success')
                    ->icon('heroicon-o-document-check'),
                Tables\Columns\TextColumn::make('addedBy.USER_NAME')->label('أضيف بواسطة'),
                Tables\Columns\TextColumn::make('RECORDED_ON')->label('تاريخ الإضافة')->dateTime()->sortable(),
                Tables\Columns\IconColumn::make('ACCEPT')->label('مقبول')->boolean(),
                Tables\Columns\IconColumn::make('RUN_IT')->label('تم التنفيذ')->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        $ownerRecord = $livewire->getOwnerRecord();
                        $data['UNID'] = $ownerRecord->UNID;
                        $data['FACULTY_IDENT'] = $ownerRecord->FACULTY_IDENT;
                        $data['PROGRAM_IDENT'] = $ownerRecord->PROGRAM_IDENT;
                        $data['RECORDED_ON'] = now();
                        $data['ADD_BY'] = auth()->id() ?? 1;

                        return $data;
                    })
                    ->after(function (array $data, RequestAdjustOffering $record, Component $livewire) {
                        $state = $livewire->form->getState();
                        $file = $state['un_attachment'] ?? null;
                        if ($file) {
                            $filePath = is_array($file) ? array_values($file)[0] : $file;
                            if (Storage::disk('public')->exists($filePath)) {
                                $dir = public_path('uploads_pdf/un');
                                File::ensureDirectoryExists($dir);
                                file_put_contents($dir.'/req_'.$record->REQUEST_ID.'.pdf', Storage::disk('public')->get($filePath));
                                Storage::disk('public')->delete($filePath);
                            }
                        }
                    }),
            ])
            ->actions([
                Action::make('review')
                    ->label('مراجعة الطلب (للوزارة)')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (RequestAdjustOffering $record) => is_null($record->ACCEPT))
                    ->form([
                        Forms\Components\Select::make('ACCEPT')
                            ->label('القرار')
                            ->options([
                                '1' => 'موافق وتم التنفيذ',
                                '0' => 'مرفوض',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\Textarea::make('REASON')
                            ->label('سبب الرفض')
                            ->visible(fn (Get $get) => $get('ACCEPT') == '0')
                            ->required(fn (Get $get) => $get('ACCEPT') == '0'),

                        Forms\Components\FileUpload::make('ministry_attachment')
                            ->label('مرفق الوزارة (وثيقة الاعتماد أو الرفض)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ])
                    ->action(function (array $data, RequestAdjustOffering $record) {
                        $file = $data['ministry_attachment'] ?? null;
                        if ($file) {
                            $filePath = is_array($file) ? array_values($file)[0] : $file;
                            if (Storage::disk('public')->exists($filePath)) {
                                $dir = public_path('uploads_pdf/ministry');
                                File::ensureDirectoryExists($dir);
                                file_put_contents($dir.'/req_'.$record->REQUEST_ID.'.pdf', Storage::disk('public')->get($filePath));
                                Storage::disk('public')->delete($filePath);
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
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
