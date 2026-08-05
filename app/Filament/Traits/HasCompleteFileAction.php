<?php

namespace App\Filament\Traits;

use Filament\Actions\Action;

trait HasCompleteFileAction
{
    protected function getCompleteFileAction(): Action
    {
        return Action::make('completeFile')
            ->label('إكمال الملف وإصدار الحافظة')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn(\App\Models\Applicant $record) => auth()->user()->can('CompleteFile:Applicant') && $record->STATUS !== \App\Enums\ApplicantStatus::Ready)
            ->requiresConfirmation()
            ->modalHeading('تأكيد إكمال الملف')
            ->modalDescription('هل أنت متأكد من رغبتك في إكمال ملف هذا المتقدم وإصدار حافظة التوريد؟')
            ->modalSubmitActionLabel('إصدار الحافظة')
            ->action(function (\App\Models\Applicant $record) {
                // 1. Check applications exist
                if ($record->applications()->count() === 0) {
                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('خطأ في إكمال الملف')
                        ->body('لا توجد رغبات (تنسيقات) مضافة لهذا المتقدم.')
                        ->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                // 2. Check for NEW unpaid desires
                $hasNewApplications = $record->applications()->where(function ($q) {
                    $q->whereNull('PAYMENT_FLAG')->orWhere('PAYMENT_FLAG', 0);
                })->exists();

                if (!$hasNewApplications) {
                    \Filament\Notifications\Notification::make()
                        ->danger()
                        ->title('خطأ في إكمال الملف')
                        ->body('لا توجد رغبات جديدة تتطلب إصدار حافظة (جميع الرغبات الحالية مسددة).')
                        ->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                // 3. If Type B (External High School), check certificate approval and attachment
                if ($record->APPLICANT_TYPE == 2) {
                    $typeBRecord = \App\Models\HighSchoolDegreeBType::where('SEC_SCHOOL_YEAR', $record->SEC_SCHOOL_YEAR)
                        ->where('SEC_SCHOOL_SEATNO', $record->SEC_SCHOOL_SEATNO)
                        ->where('UNID', $record->UNID)
                        ->first();

                    if (!$typeBRecord) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('بيانات شهادة الثانوية (نوع ب) غير موجودة.')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }

                    if ($typeBRecord->APPROVED != 1) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('شهادة الثانوية (نوع ب) غير معتمدة أو تم إيقاف اعتمادها.')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }

                    if (empty($typeBRecord->SEC_SCHOOL_CERTIFICATE)) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('المتقدم من نوع شهادة (ب) ولم يتم إرفاق صورة شهادة الثانوية في قاعدة البيانات.')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }

                    // Physical file check for Type B
                    $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                    $certPath = "uploads/{$portalPrefix}/images/attachments/secondary/{$typeBRecord->SEC_SCHOOL_CERTIFICATE}.jpg";
                    if (!\Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'))->exists($certPath)) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('ملف صورة الشهادة الثانوية غير موجود فيزيائياً على الخادم.')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }
                }

                // 3. If Clearing (مقاصة)
                if ($record->IS_CLEARING->value !== 0) {
                    // Check if clearing data exists
                    if (!$record->applicationsClearing()->exists()) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('لم يتم إكمال بيانات الجامعة السابقة (المقاصة).')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }
                    
                    // Check if attachments (3 and 4) are uploaded physically
                    $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                    $gradesPath = "uploads/{$portalPrefix}/images/attachments/grades/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                    $clearingPath = "uploads/{$portalPrefix}/images/attachments/clearing/{$record->UNID}-{$record->APPLICANT_IDENT}.pdf";
                    
                    $disk = \Illuminate\Support\Facades\Storage::disk(config('legacy_attachments.disk', 'public'));
                    if (!$disk->exists($gradesPath) || !$disk->exists($clearingPath)) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('لم يتم رفع الوثائق المطلوبة للمقاصة فيزيائياً (السجل الأكاديمي، توصيف المقررات).')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }
                } else {
                    // 4. If not clearing, check MIN/MAX constraints per offer group
                    $appGroups = $record->applications()
                        ->join('applications_group', 'applications.APP_BILL_IDENT', '=', 'applications_group.APP_BILL_IDENT')
                        ->selectRaw('applications_group.OFFER_GROUP_IDENT, COUNT(*) as apps_count, applications_group.APP_BILL_IDENT')
                        ->groupBy('applications_group.OFFER_GROUP_IDENT', 'applications_group.APP_BILL_IDENT')
                        ->get();

                    $conflicts = [];
                    foreach ($appGroups as $group) {
                        $offerGroup = \App\Models\OfferingGroup::find($group->OFFER_GROUP_IDENT);
                        if ($offerGroup) {
                            if ($group->apps_count < $offerGroup->MIN_CHOICE || $group->apps_count > $offerGroup->MAX_CHOICE) {
                                $conflicts[] = "عدد الرغبات ({$group->apps_count}) غير مطابق للشروط (من {$offerGroup->MIN_CHOICE} إلى {$offerGroup->MAX_CHOICE}) في إحدى مجموعات الرغبات.";
                            }
                        }
                    }

                    if (count($conflicts) > 0) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('تعارض في عدد الرغبات')
                            ->body(implode('<br>', $conflicts))
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }
                }

                // If all validations pass
                $record->applicationGroups()->update(['IS_ENABLE' => 1]);

                $record->update([
                    'STATUS' => \App\Enums\ApplicantStatus::Ready,
                    'FREEZE' => \App\Enums\FreezeStatus::FROZEN,
                ]);

                if ($record->IS_CLEARING->value !== 0) {
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('تم إكمال الملف بنجاح')
                        ->body('تم رفع الملف للمصادقة والمراجعة. يرجى الانتظار لحين اعتماد الوزارة لتتمكن من طباعة الحافظة.')
                        ->send();

                    // Notify super_admins or specific reviewers
                    try {
                        $usersToNotify = \App\Models\User::role('super_admin')->get();
                        \Filament\Notifications\Notification::make()
                            ->title('ملف مقاصاة جديد للمراجعة')
                            ->body("تم إكمال ملف الطالب {$record->APPLICANT_NAME} وهو الآن بانتظار مصادقة الجامعة.")
                            ->success()
                            ->sendToDatabase($usersToNotify);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Notification failed: ' . $e->getMessage());
                    }
                } else {
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('تم إكمال الملف بنجاح')
                        ->body('سيتم تحويلك لطباعة حافظة التوريد.')
                        ->send();

                    $this->redirect(route('applicant.receipt', ['unid' => $record->UNID, 'applicant_ident' => $record->APPLICANT_IDENT]));
                }
            });
    }
}
