<?php

namespace App\Filament\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use App\Filament\Filters\AcademicFilter;
use App\Filament\Resources\Applications\Pages\ListApplications;
use App\Models\AppBillIdentCanceled;
use App\Models\ApplicationGroup;
use App\Models\OfferingGroup;
use App\Models\StudyType;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->columns([
                TextColumn::make('index')
                    ->rowIndex()
                    ->label('مسلسل'),
                TextColumn::make('UNID')
                    ->label(__('UNID'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT'))
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('APPLICANT_IDENT')
                //     ->label(__('APPLICANT_IDENT'))
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('applicant.FULL_NAME')
                    ->label('المتقدم')
                    ->searchable(['FULL_NAME', 'MOBILE_PHONE', 'APPLICANT_IDENT', 'SEC_SCHOOL_SEATNO'])
                    ->url(fn($record) => $record->applicant ? \App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $record->applicant]) : null)
                    ->tooltip('انقر هنا للانتقال إلى ملف المتقدم')
                    ->openUrlInNewTab()
                    ->color('primary')
                    ->extraAttributes(['class' => 'underline font-bold'])
                    ->visibleOn(ListApplications::class),
                TextColumn::make('faculty.FACULTY_NAME')
                    ->label('الكلية')
                    ->words(4)
                    ->searchable(),
                TextColumn::make('program.PROGRAM_NAME')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('SEC_SCHOOL_RATE')
                    ->label('معدل الثانوية')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('studyType.STUDYTYPE_NAME')
                    ->label('النظام الدراسي')
                    ->searchable(),
                TextColumn::make('PAYMENT_FLAG')
                    ->label('طريقة الدفع')
                    ->badge()
                    ->searchable(),
                TextColumn::make('APP_BILL_IDENT')
                    ->label('رقم السند')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('RECORDDATE')
                    ->label('تاريخ التسديد / الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('insertedBy.USER_NAME')
                    ->label('بواسطة')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('CONFIRMED_ON')
                    ->label('تاريخ التأكيد')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('CHOICE_NO')
                    ->label('رقم الرغبة')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('FINAL_MARK')
                    ->label('المفاضلة')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('STUDENT_CODE')
                    ->label('رقم الطالب')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('EXPORTED')
                    ->label('مُصدَّر (EXPORTED)')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ColumnGroup::make('مقبول | مؤكد')
                    ->columns([
                        IconColumn::make('ACCEPTED')
                            ->label('مقبول')
                            ->boolean()
                            ->trueColor('info')
                            ->falseColor('gray')
                            ->sortable()
                            ->action(
                                Action::make('toggleAcceptColumn')
                                    ->disabled(fn($record) => (bool)$record->ACCEPTED
                                        ? (! auth()->user()->can('cancelAccept', $record) || (bool)$record->CONFIRMED_BY_APPLICANT)
                                        : (! auth()->user()->can('accept', $record) || empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE)
                                    )
                                    ->requiresConfirmation(fn($record) => (bool)$record->ACCEPTED && ! (bool)$record->CONFIRMED_BY_APPLICANT)
                                    ->modalHeading('إلغاء قبول الرغبة')
                                    ->modalDescription('هل أنت متأكد من إلغاء قبول هذه الرغبة؟ سيتم إعادة حالة الطلب إلى غير مقبول.')
                                    ->modalSubmitActionLabel('نعم، إلغاء القبول')
                                    ->action(function ($record) {
                                        if ((bool)$record->ACCEPTED) {
                                            if (! auth()->user()->can('cancelAccept', $record)) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('ليس لديك صلاحية لإلغاء القبول')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            if ((bool)$record->CONFIRMED_BY_APPLICANT) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('لا يمكن إلغاء القبول')
                                                    ->body('لا يمكن إلغاء قبول هذه الرغبة لأنها مؤكدة حالياً برقم قيد جامعي. يجب إلغاء التأكيد أولاً!')
                                                    ->warning()
                                                    ->send();
                                                return;
                                            }

                                            $record->cancelAccept();

                                            \Filament\Notifications\Notification::make()
                                                ->title('تم إلغاء القبول بنجاح')
                                                ->body('تم تحويل حالة الرغبة إلى غير مقبولة.')
                                                ->success()
                                                ->send();
                                        } else {
                                            if (! auth()->user()->can('accept', $record)) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('ليس لديك صلاحية')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            if (empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('تنبيه')
                                                    ->body('لا يمكن قبول الرغبة قبل تسديد الرسوم.')
                                                    ->warning()
                                                    ->send();
                                                return;
                                            }

                                            $record->accept();

                                            \Filament\Notifications\Notification::make()
                                                ->title('تم قبول الرغبة بنجاح')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            ),
                        IconColumn::make('CONFIRMED_BY_APPLICANT')
                            ->label('مؤكد')
                            ->boolean()
                            ->trueColor('success')
                            ->falseColor('gray')
                            ->sortable()
                            ->action(
                                Action::make('toggleConfirmColumn')
                                    ->disabled(fn($record) => ! (bool)$record->CONFIRMED_BY_APPLICANT || ! auth()->user()->can('cancelConfirm', $record))
                                    ->requiresConfirmation(fn($record) => (bool)$record->CONFIRMED_BY_APPLICANT)
                                    ->modalHeading('إلغاء تأكيد الرغبة')
                                    ->modalDescription('هل أنت متأكد من إلغاء تأكيد هذه الرغبة وتصفير رقم القيد والمقعد المحجوز؟')
                                    ->modalSubmitActionLabel('نعم، إلغاء التأكيد')
                                    ->action(function ($record) {
                                        if ((bool)$record->CONFIRMED_BY_APPLICANT) {
                                            if (! auth()->user()->can('cancelConfirm', $record)) {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('ليس لديك صلاحية لإلغاء التأكيد')
                                                    ->danger()
                                                    ->send();
                                                return;
                                            }

                                            $record->cancelConfirm();

                                            \Filament\Notifications\Notification::make()
                                                ->title('تم إلغاء التأكيد بنجاح')
                                                ->body('تم إلغاء تأكيد الرغبة وتصفير رقم القيد والمقعد المحجوز.')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            ),
                    ]),
                TextColumn::make('STATUS')
                    ->label('الحالة')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                AcademicFilter::make(),
                SelectFilter::make('PAYMENT_FLAG')
                    ->label('طريقة الدفع')
                    ->relationship('paymentMethod', 'PAY_METHOD'),
                SelectFilter::make('STUDYTYPE_IDENT')
                    ->label('النظام الدراسي')
                    ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT')),
                SelectFilter::make('STATUS')
                    ->label('الحالة')
                    ->options(ApplicationStatus::class),
                TernaryFilter::make('ACCEPTED')
                    ->label('حالة القبول')
                    ->placeholder('الكل')
                    ->trueLabel('مقبول')
                    ->falseLabel('غير مقبول'),
                TernaryFilter::make('CONFIRMED_BY_APPLICANT')
                    ->label('حالة التأكيد')
                    ->placeholder('الكل')
                    ->trueLabel('مؤكد')
                    ->falseLabel('غير مؤكد'),
                TernaryFilter::make('is_paid')
                    ->label('حالة التسديد')
                    ->placeholder('الكل')
                    ->trueLabel('مسدد')
                    ->falseLabel('غير مسدد')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNotNull('PAYMENT_FLAG')->where('PAYMENT_FLAG', '!=', 0),
                        false: fn(Builder $query) => $query->where(fn(Builder $q) => $q->whereNull('PAYMENT_FLAG')->orWhere('PAYMENT_FLAG', 0)),
                        blank: fn(Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('accept')
                    ->label('قبول')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->visible(fn($record) => !empty($record->PAYMENT_FLAG) && $record->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE && ! (bool)$record->ACCEPTED)
                    ->hidden(fn($record) => ! auth()->user()->can('accept', $record))
                    ->requiresConfirmation()
                    ->modalHeading('قبول الرغبة')
                    ->modalDescription('هل أنت متأكد من قبول وترشيح هذه الرغبة؟')
                    ->modalSubmitActionLabel('قبول')
                    ->action(function ($record) {
                        $record->accept();

                        \Filament\Notifications\Notification::make()
                            ->title('تم قبول الرغبة بنجاح')
                            ->success()
                            ->send();
                    }),

                Action::make('cancelAccept')
                    ->label('إلغاء القبول')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn($record) => (bool)$record->ACCEPTED && ! (bool)$record->CONFIRMED_BY_APPLICANT)
                    ->hidden(fn($record) => ! auth()->user()->can('cancelAccept', $record))
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء قبول الرغبة')
                    ->modalDescription('هل أنت متأكد من إلغاء قبول هذه الرغبة؟')
                    ->modalSubmitActionLabel('إلغاء القبول')
                    ->action(function ($record) {
                        $record->cancelAccept();

                        \Filament\Notifications\Notification::make()
                            ->title('تم إلغاء القبول بنجاح')
                            ->body('تم تحويل حالة الرغبة إلى غير مقبولة.')
                            ->success()
                            ->send();
                    }),

                Action::make('printReceipt')
                    ->label('طباعة الحافظة')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn($record) => (empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE) && $record->applicant && $record->applicant->FREEZE === \App\Enums\FreezeStatus::FROZEN)
                    ->hidden(fn($record) => ! auth()->user()->can('printReceipt', $record))
                    ->url(fn($record) => route('applicant.receipt', ['unid' => $record->UNID, 'applicant_ident' => $record->APPLICANT_IDENT]))
                    ->openUrlInNewTab(),

                Action::make('pay')
                    ->label('تسديد')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->visible(fn($record) => (empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE))
                    ->hidden(fn($record) => ! auth()->user()->can('pay', $record))
                    ->modalHeading(function ($record) {
                        $program = $record->program?->PROGRAM_NAME ?? $record->APPLICATION_IDENT;
                        $studyType = $record->studyType?->STUDYTYPE_NAME;
                        $applicantName = $record->applicant?->FULL_NAME;

                        $parts = array_filter([$program, $studyType]);
                        $heading = "تسديد رسوم الرغبة [ " . implode(' - ', $parts) . " ]";

                        if ($applicantName) {
                            $heading .= " - " . $applicantName;
                        }

                        return $heading;
                    })
                    ->modalSubmitAction(function ($action, $record) {
                        $status = static::getPaymentPeriodStatus($record);
                        return $status['isOpen'] ? $action->label('حفظ التسديد')->color('warning') : false;
                    })
                    ->schema(function ($record) {
                        $status = static::getPaymentPeriodStatus($record);

                        if (! $status['isOpen']) {
                            $startedHtml = $status['started'] ? "<span><strong>تاريخ بداية السداد:</strong> {$status['started']}</span>" : "";
                            $finishedHtml = $status['finished'] ? "<span><strong>تاريخ نهاية السداد:</strong> {$status['finished']}</span>" : "";

                            return [
                              Callout::make('حالة التسديد مقفلة')
                                    // ->visible(fn(Get $get) => (bool) $get('has_answered_project_survey'))
                                    ->description($status['message'])
                                    ->danger()
                                    ->icon('heroicon-o-check-badge')
                                    ->columnSpan(6),
                                
                            ];
                        }

                        return [
                              Callout::make('حالة التسديد متاحة')
                                    // ->visible(fn(Get $get) => (bool) $get('has_answered_project_survey'))
                                    ->description($status['message'])
                                    ->success()
                                    ->icon('heroicon-o-check-badge')
                                    ->columnSpan(6),
                                
                           
                            
                            Select::make('PAYMENT_FLAG')
                                ->label('طريقة الدفع')
                                ->options(function (\App\Models\Application $record) {
                                    $uni = $record->university;
                                    $allowedIds = [];
                                    if ($uni) {
                                        // 1: البريد, 2: كاك بنك, 3: مسؤل التحصيل في الجامعة
                                        if ($uni->PAY_METHOD_POST == 1) $allowedIds[] = 1;
                                        if ($uni->PAY_METHOD_CAC == 1) $allowedIds[] = 2;
                                        if ($uni->PAY_METHOD_UN == 1) $allowedIds[] = 3;
                                    }
                                    return \App\Models\PaymentMethod::whereIn('PAY_METHOD_ID', $allowedIds)
                                        ->where('IS_ENABLED', 1)
                                        ->pluck('PAY_METHOD', 'PAY_METHOD_ID');
                                })
                                ->required(),
                            TextInput::make('APP_BILL_IDENT')
                                ->label('رقم السند')
                                ->required(),
                            DatePicker::make('RECORDDATE')
                                ->label('تاريخ التسديد')
                                ->default(now())
                                ->required(),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $status = static::getPaymentPeriodStatus($record);
                        if (! $status['isOpen']) {
                            \Filament\Notifications\Notification::make()
                                ->title('فشل التسديد')
                                ->body($status['message'])
                                ->danger()
                                ->send();
                            throw new \Filament\Support\Exceptions\Halt();
                        }

                        $record->update([
                            'PAYMENT_FLAG' => $data['PAYMENT_FLAG'],
                            'APP_BILL_IDENT' => $data['APP_BILL_IDENT'],
                            'RECORDDATE' => $data['RECORDDATE'],
                            'INSERTED_BY' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('تم تسجيل التسديد بنجاح')
                            ->success()
                            ->send();
                    }),

                Action::make('cancelPayment')
                    ->label('إلغاء السداد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => !empty($record->PAYMENT_FLAG) && $record->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE && ! (bool)$record->ACCEPTED && ! (bool)$record->CONFIRMED_BY_APPLICANT)
                    ->hidden(fn($record) => ! auth()->user()->can('cancelPayment', $record))
                    ->modalHeading('إلغاء الحافظة المالية المسددة')
                    ->modalDescription(fn($record) => "رقم الحافظة: [ {$record->APP_BILL_IDENT} ] - رقم الطلب: [ {$record->APPLICATION_IDENT} ]")
                    ->schema([
                        Textarea::make('NOTE')
                            ->label('سبب وتوجيه الإلغاء')
                            ->placeholder('يجب كتابة مصدر التوجيه ورقم الوارد وسبب إلغاء الحافظة')
                            ->rows(4)
                            ->required(),
                        Checkbox::make('CONFIRMATION')
                            ->label(fn($record) => "تأكيد إلغاء الحافظة المالية رقم ({$record->APP_BILL_IDENT}) وتصفير السداد")
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        DB::beginTransaction();
                        try {
                            $billIdent = $record->APP_BILL_IDENT;
                            $appGroup = ApplicationGroup::where('APP_BILL_IDENT', $billIdent)->first();

                            // 1. Save backup of canceled bill
                            AppBillIdentCanceled::create([
                                'APP_BILL_IDENT' => $billIdent,
                                'PAYMENT' => $appGroup?->PAYMENT ?? $appGroup?->APPLYING_COST ?? 0,
                                'BONDS_ID' => $appGroup?->BONDS_ID ?? '0',
                                'BONDS_DATE' => $appGroup?->BONDS_DATE ?? now(),
                                'PAYMENT_FLAG' => is_object($record->PAYMENT_FLAG) ? $record->PAYMENT_FLAG->value : ($record->PAYMENT_FLAG ?? 1),
                                'PAY_METHOD_ID' => $appGroup?->PAY_METHOD_ID ?? (is_object($record->PAYMENT_FLAG) ? $record->PAYMENT_FLAG->value : 0),
                                'PAYMENT_BY' => $appGroup?->PAYMENT_BY ?? auth()->id(),
                                'ACTUAL_PAYMENT_DATE' => $appGroup?->ACTUAL_PAYMENT_DATE ?? now(),
                                'NOTE' => $data['NOTE'],
                                'CANCELED_BY' => auth()->id(),
                                'CANCELED_ON' => now(),
                            ]);

                            // 2. Update all Applications attached to this bill
                            \App\Models\Application::where('APP_BILL_IDENT', $billIdent)->update([
                                'PAYMENT_FLAG' => 0,
                                'STATUS' => \App\Enums\ApplicationStatus::New,
                                'CONFIRMED_BY_APPLICANT' => 0,
                                'STUDENT_CODE' => '0',
                            ]);

                            // 3. Update ApplicationGroup
                            if ($appGroup) {
                                $appGroup->update([
                                    'PAYMENT' => 0,
                                    'ACTUAL_PAYMENT_DATE' => null,
                                    'PAY_METHOD_ID' => 0,
                                ]);
                            }

                            // 4. Update applicant status and freeze
                            if ($record->applicant) {
                                $record->applicant->update([
                                    'STATUS' => \App\Enums\ApplicantStatus::Updated,
                                    'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                                ]);
                            }

                            DB::commit();

                            \Filament\Notifications\Notification::make()
                                ->title('تم إلغاء السداد بنجاح')
                                ->body("تم إلغاء بيانات الحافظة رقم ({$billIdent}) وأرشفة العملية بنجاح.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            DB::rollBack();
                            \Filament\Notifications\Notification::make()
                                ->title('حدث خطأ أثناء إلغاء السداد')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                            throw new \Filament\Support\Exceptions\Halt();
                        }
                    }),

                Action::make('confirm')
                    ->label('تأكيد')
                    ->icon('heroicon-o-check-badge')
                    ->color('info')
                    ->visible(fn($record) => !empty($record->PAYMENT_FLAG) && $record->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE && (bool)$record->ACCEPTED && ! (bool)$record->CONFIRMED_BY_APPLICANT)
                    ->hidden(fn($record) => ! auth()->user()->can('confirm', $record))
                    ->requiresConfirmation()
                    ->schema([
                         TextInput::make('STUDENT_CODE')
                            ->label('رقم القيد الجامعي')
                            ->required()
                            ->rules([
                                fn($record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                                    $exists = \App\Models\Application::where('UNID', $record->UNID)
                                        ->where('STUDENT_CODE', $value)
                                        ->where('APPLICATION_IDENT', '!=', $record->APPLICATION_IDENT)
                                        ->exists();
                                    if ($exists) {
                                        $fail("رقم القيد ({$value}) مستخدم بالفعل لطالب آخر في نفس الجامعة.");
                                    }
                                }
                            ])
                    ])
                    ->action(function ($record, array $data) {
                        $unid = $record->UNID;
                        $programIdent = $record->PROGRAM_IDENT;
                        $studytypeIdent = $record->STUDYTYPE_IDENT;

                        // get capacity
                        $capacity = \App\Models\ProgramCapacity::where('UNID', $unid)
                            ->where('PROGRAM_IDENT', $programIdent)
                            ->where('STUDYTYPE_IDENT', $studytypeIdent)
                            ->value('ENROLLMENT_CAPACITY') ?? 0;

                        // get current count
                        $currentCount = \App\Models\Application::where('UNID', $unid)
                            ->where('PROGRAM_IDENT', $programIdent)
                            ->where('STUDYTYPE_IDENT', $studytypeIdent)
                            ->where('PAYMENT_FLAG', '!=', 0)
                            ->whereNotNull('PAYMENT_FLAG')
                            ->where('CONFIRMED_BY_APPLICANT', 1)
                            ->count();

                        if ($currentCount < $capacity) {
                            $record->confirmApplication($data['STUDENT_CODE']);

                            \Filament\Notifications\Notification::make()
                                ->title('تم تأكيد الطلب بنجاح')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('لا يمكن التأكيد')
                                ->body("الطاقة الاستيعابية في هذا التخصص ممتلئة بعدد {$currentCount} متقدم تم تأكيدهم مسبقاً.")
                                ->danger()
                                ->send();
                            throw new \Filament\Support\Exceptions\Halt();
                        }
                    }),

                Action::make('cancelConfirm')
                    ->label('إلغاء التأكيد')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => (bool)$record->CONFIRMED_BY_APPLICANT)
                    ->hidden(fn($record) => ! auth()->user()->can('cancelConfirm', $record))
                    ->requiresConfirmation()
                    ->modalHeading('إلغاء تأكيد الرغبة')
                    ->modalDescription('هل أنت متأكد من إلغاء تأكيد هذه الرغبة وتصفير رقم القيد والمقعد المحجوز؟')
                    ->modalSubmitActionLabel('إلغاء التأكيد')
                    ->action(function ($record) {
                        $record->cancelConfirm();

                        \Filament\Notifications\Notification::make()
                            ->title('تم إلغاء تأكيد المتقدم')
                            ->body('تم تصفير رقم القيد والمقعد المحجوز بنجاح.')
                            ->success()
                            ->send();
                    }),

                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->visible(fn($record) => empty($record->PAYMENT_FLAG) || $record->PAYMENT_FLAG === \App\Enums\PaymentMethodEnum::NONE),
                ]),
            ])
            ->defaultSort('SEC_SCHOOL_RATE', 'desc');
    }

    public static function getPaymentPeriodStatus($record): array
    {
        $offerGroup = $record->offeringGroup ?? OfferingGroup::where('OFFER_GROUP_IDENT', $record->OFFER_GROUP_IDENT)->first();

        if (! $offerGroup && $record->offering) {
            $offerGroup = $record->offering->offeringGroup;
        }

        if (! $offerGroup) {
            return [
                'isOpen' => false,
                'title' => 'إعدادات مجموعة التنسيق غير متوفرة',
                'message' => 'لم يتم العثور على مجموعة معايير التنسيق المرتبطة بهذه الرغبة.',
                'started' => null,
                'finished' => null,
            ];
        }

        $enablePayment = (bool) ($offerGroup->ENABLE_PAYMENT ?? false);
        $startedDate = $offerGroup->STARTED_PAYMENT_DATE;
        $finishedDate = $offerGroup->FINISHED_PAYMENT_DATE;
        $now = now();

        $formattedStarted = $startedDate ? Carbon::parse($startedDate)->format('Y-m-d') : 'غير محدد';
        $formattedFinished = $finishedDate ? Carbon::parse($finishedDate)->format('Y-m-d h:i A') : 'غير محدد';

        if (! $enablePayment) {
            return [
                'isOpen' => false,
                'title' => 'التسديد غير مفعل حالياً',
                'message' => 'تم إيقاف تفعيل التسديد لمجموعة هذا التخصص من قِبل إدارة القبول والتسجيل.',
                'started' => $formattedStarted,
                'finished' => $formattedFinished,
            ];
        }

        if ($startedDate && $now->lt(Carbon::parse($startedDate)->startOfDay())) {
            return [
                'isOpen' => false,
                'title' => 'فترة التسديد لم تبدأ بعد',
                'message' => "فترة السداد المعتمدة تبدأ بتاريخ: {$formattedStarted}",
                'started' => $formattedStarted,
                'finished' => $formattedFinished,
            ];
        }

        if ($finishedDate && $now->gt(Carbon::parse($finishedDate))) {
            return [
                'isOpen' => false,
                'title' => 'فترة التسديد منتهية ومقفلة',
                'message' => "انتهت فترة السداد المعتمدة بتاريخ: {$formattedFinished}",
                'started' => $formattedStarted,
                'finished' => $formattedFinished,
            ];
        }

        return [
            'isOpen' => true,
            'title' => 'فترة التسديد مفتوحة ومتاحة',
            'message' => "يمكنك تسديد الرسوم المقرة للرغبة الحالية ولا يزال التسديد متاح حتى : {$formattedFinished}",
            'started' => $formattedStarted,
            'finished' => $formattedFinished,
        ];
    }
}
