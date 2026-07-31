<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;



class ViewApplicant extends ViewRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getApiRefreshAction(),
            $this->getCompleteFileAction(),
            EditAction::make()
                ->visible(fn (\App\Models\Applicant $record) => $record->STATUS !== \App\Enums\ApplicantStatus::Ready || auth()->user()->isAdmin()),
        ];
    }

    protected function getCompleteFileAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('completeFile')
            ->label('إكمال الملف وإصدار الحافظة')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn (\App\Models\Applicant $record) => $record->STATUS !== \App\Enums\ApplicantStatus::Ready)
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
                            ->body('المتقدم من نوع شهادة (ب) ولم يتم إرفاق صورة شهادة الثانوية.')
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
                    
                    // Check if attachments (3 and 4) are uploaded
                    $clearingAttachments = $record->applicantAttachments()->whereIn('ATTACH_IDENT', [3, 4])->count();
                    if ($clearingAttachments < 2) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('خطأ في إكمال الملف')
                            ->body('لم يتم رفع الوثائق المطلوبة للمقاصة (السجل الأكاديمي، توصيف المقررات).')
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

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('تم إكمال الملف بنجاح')
                    ->body('سيتم تحويلك لطباعة حافظة التوريد.')
                    ->send();

                $this->redirect(route('applicant.receipt', ['unid' => $record->UNID, 'applicant_ident' => $record->APPLICANT_IDENT]));
            });
    }

    protected function getApiRefreshAction(): \Filament\Actions\Action
    {
        return  Action::make('fetchApi')
            ->label('تحديث من الوزارة')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(fn (\App\Models\Applicant $record) => auth()->user()->can('UpdateFromMinistryApplicant:Applicant') && ($record->STATUS !== \App\Enums\ApplicantStatus::Ready || auth()->user()->isAdmin()))
            ->modalHeading('بيانات المتقدم من الوزارة')
            ->modalSubmitActionLabel('تحديث السجل')
            ->modalCancelActionLabel('إلغاء')
            ->fillForm(function (\App\Models\Applicant $record) {
                $seat = $record->SEC_SCHOOL_SEATNO;
                $year = $record->SEC_SCHOOL_YEAR;
                $mark = intval($record->SEC_SCHOOL_MARK);

                $apiService = new \App\Services\MinistryApiService();
                $data = $apiService->fetchStudentData($year, $seat, $mark);
                $found = false;
                $updates = [];

                if (!empty($data) && (isset($data['seat_number']) || (is_array($data) && count($data) > 0))) {
                    $found = true;
                    $student = isset($data[0]) ? $data[0] : $data;

                                $nameParts = explode(' ', trim($student['name'] ?? ''));

                                $updates = [
                                    'FULL_NAME' => $student['name'] ?? '',
                                    'FIRST_NAME' => $nameParts[0] ?? '',
                                    'LAST_NAME' => end($nameParts) ?? '',
                                    'SEC_SCHOOL_NAME' => $student['idSchool'] ?? $student['school_name'] ?? '',
                                    'SEC_SCHOOL_RATE' => $student['Rate'] ?? $student['rate'] ?? '',
                                    'GENDER' => $student['gender'] ?? '',
                                    'PLACE_OF_BIRTH' => $student['city_birth'] ?? '',
                                    'COUNTRY_NAME' => $student['nationality'] ?? '',
                                    'YEMEN_NATIONAL' => ($student['nationality'] ?? '') == 'يمني' ? 1 : 0,
                                ];

                                $sec = $student['section_id'] ?? 1; // Default
                                if ($sec == 1 || ($student['type'] ?? '') === 'علمي') {
                                    $updates['SEC_SCHOOL_TYPE'] = 'علمي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 2 || ($student['type'] ?? '') === 'أدبي' || ($student['type'] ?? '') === 'ادبي') {
                                    $updates['SEC_SCHOOL_TYPE'] = 'ادبي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 3 || str_contains($student['type'] ?? '', 'شرعي')) {
                                    $updates['SEC_SCHOOL_TYPE'] = 'علوم شرعي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 1700;
                                }

                                if (isset($student['bod'])) {
                                    $updates['DATE_OF_BIRTH'] = date('Y-m-d', strtotime(str_replace('/', '-', $student['bod'])));
                                }

                                $cityAndProvince = explode('/', $student['city_study'] ?? '');
                                $lastPos = count($cityAndProvince) > 1 ? (count($cityAndProvince) - 1) : 0;
                                $updates['SEC_SCHOOL_TERRITORY'] = trim($cityAndProvince[0] ?? '');
                                $updates['SEC_SCHOOL_PROVINCE'] = trim($cityAndProvince[$lastPos] ?? '');
                                $updates['SEC_SCHOOL_PLACE'] = $student['city_study'] ?? '';

                                $Territory = explode('/', $student['city_birth'] ?? '');
                                $Province = count($Territory) > 1 ? (count($Territory) - 1) : 0;
                                $updates['TERRITORY'] = trim($Territory[0] ?? '');
                                $updates['PROVINCE'] = trim(trim($Territory[$Province] ?? ''));

                                }
                if (!$found) {
                    \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على بيانات هذا المتقدم في الوزارة!')->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                return $updates;
            })
            ->form([
                 Grid::make(2)->schema([

                     Section::make('البيانات الواردة من الوزارة')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('FULL_NAME')->inlineLabel()
                                ->label('الاسم كامل')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->FULL_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_TYPE')
                                ->label('نوع الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_TYPE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_RATE')
                                ->label('المعدل')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_RATE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_NAME')
                                ->label('اسم المدرسة')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_PROVINCE')
                                ->label('محافظة الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_PROVINCE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_TERRITORY')
                                ->label('مديرية الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_TERRITORY ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('GENDER')
                                ->label('الجنس')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->GENDER ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('DATE_OF_BIRTH')
                                ->label('تاريخ الميلاد')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->DATE_OF_BIRTH ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('PLACE_OF_BIRTH')
                                ->label('مكان الميلاد')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->PLACE_OF_BIRTH ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('COUNTRY_NAME')
                                ->label('الجنسية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->COUNTRY_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),

                            // Hidden necessary fields
                            Hidden::make('FIRST_NAME'),
                            Hidden::make('LAST_NAME'),
                            Hidden::make('YEMEN_NATIONAL'),
                            Hidden::make('SEC_SCHOOL_OVERALLMARK'),
                            Hidden::make('SEC_SCHOOL_PLACE'),
                            Hidden::make('TERRITORY'),
                            Hidden::make('PROVINCE'),
                        ])->inlineLabel(),

                    Section::make('البيانات الحالية في النظام')
    ->columnSpan(1)
    ->schema([
        TextInput::make('FULL_NAME')
            ->label('الاسم كامل') ->inlineLabel()
            ->disabled()
            ->default(fn ($record) => $record->FULL_NAME ?: '-'),
        TextInput::make('SEC_SCHOOL_TYPE')->inlineLabel()
            ->label('نوع الثانوية')
            ->disabled()
            ->default(fn ($record) => $record->SEC_SCHOOL_TYPE ?: '-'),
        TextInput::make('SEC_SCHOOL_RATE')
            ->label('المعدل')
            ->disabled()
            ->default(fn ($record) => $record->SEC_SCHOOL_RATE ?: '-'),
        TextInput::make('SEC_SCHOOL_NAME')
            ->label('اسم المدرسة')
            ->disabled()
            ->default(fn ($record) => $record->SEC_SCHOOL_NAME ?: '-'),
        TextInput::make('SEC_SCHOOL_PROVINCE')
            ->label('محافظة الثانوية')
            ->disabled()
            ->default(fn ($record) => $record->SEC_SCHOOL_PROVINCE ?: '-'),
        TextInput::make('SEC_SCHOOL_TERRITORY')
            ->label('مديرية الثانوية')
            ->disabled()
            ->default(fn ($record) => $record->SEC_SCHOOL_TERRITORY ?: '-'),
        TextInput::make('GENDER')
            ->label('الجنس')
            ->disabled()
            ->default(fn ($record) => $record->GENDER ?: '-'),
        TextInput::make('DATE_OF_BIRTH')
            ->label('تاريخ الميلاد')
            ->disabled()
            ->default(fn ($record) => $record->DATE_OF_BIRTH ?: '-'),
        TextInput::make('PLACE_OF_BIRTH')
            ->label('مكان الميلاد')
            ->disabled()
            ->default(fn ($record) => $record->PLACE_OF_BIRTH ?: '-'),
        TextInput::make('COUNTRY_NAME')
            ->label('الجنسية')
            ->disabled()
            ->default(fn ($record) => $record->COUNTRY_NAME ?: '-'),
    ])->inlineLabel(),
                ])
            ])
            ->action(function (\App\Models\Applicant $record, array $data, \Filament\Resources\Pages\ViewRecord $livewire) {
                // Normalize gender (remove hamza)
                if (isset($data['GENDER'])) {
                    $data['GENDER'] = str_replace('أنثى', 'انثى', $data['GENDER']);
                }

                // Convert nationality string to YEMEN_NATIONAL flag
                if (isset($data['COUNTRY_NAME'])) {
                    $data['YEMEN_NATIONAL'] = ($data['COUNTRY_NAME'] === 'يمني') ? 1 : 0;
                }

                // Calculate FINAL_STATUS based on RATE if missing or always calculate
                if (isset($data['SEC_SCHOOL_RATE'])) {
                    $data['FINAL_STATUS'] = floatval($data['SEC_SCHOOL_RATE']) >= 50 ? 'ناجح' : 'راسب';
                }

                // Determine if there are changes beyond allowed fields
                $original = $record->getAttributes();
                $changed = array_diff_assoc($data, $original);
                unset($changed['GENDER'], $changed['YEMEN_NATIONAL']);

                // If there are other changes, save old data to history table
                if (!empty($changed)) {
                    \App\Models\HighSchoolDegreeHistory::create([
                        'SEC_SCHOOL_YEAR' => $original['SEC_SCHOOL_YEAR'] ?? 0,
                        'SEC_SCHOOL_SEATNO' => $original['SEC_SCHOOL_SEATNO'] ?? 0,
                        'STUDENT_NAME' => $original['FULL_NAME'] ?? 'غير معروف',
                        'SEC_SCHOOL_MARK' => $original['SEC_SCHOOL_MARK'] ?? 0,
                        'FINAL_STATUS' => $original['FINAL_STATUS'] ?? (floatval($original['SEC_SCHOOL_RATE'] ?? 0) >= 50 ? 'ناجح' : 'راسب'),
                        'SEC_SCHOOL_RATE' => $original['SEC_SCHOOL_RATE'] ?? 0,
                        'SEC_SCHOOL_PROVINCE' => $original['SEC_SCHOOL_PROVINCE'] ?? 'غير محدد',
                        'SEC_SCHOOL_TERRITORY' => $original['SEC_SCHOOL_TERRITORY'] ?? 'غير محدد',
                        'SEC_SCHOOL_TYPE' => $original['SEC_SCHOOL_TYPE'] ?? 'غير محدد',
                        'SEC_SCHOOL_NAME' => $original['SEC_SCHOOL_NAME'] ?? null,
                        'SEC_SCHOOL_PLACE' => $original['SEC_SCHOOL_PLACE'] ?? null,
                        'PLACE_OF_BIRTH' => $original['PLACE_OF_BIRTH'] ?? null,
                        'DATE_OF_BIRTH' => $original['DATE_OF_BIRTH'] ?? null,
                        'PROVINCE' => $original['PROVINCE'] ?? null,
                        'TERRITORY' => $original['TERRITORY'] ?? 'غير محدد',
                        'NATIONALITY' => $original['NATIONALITY'] ?? null,
                        'COUNTRY_NAME' => $original['COUNTRY_NAME'] ?? 'غير محدد',
                        'COUNTRY_IDENT' => $original['COUNTRY_IDENT'] ?? 0,
                        'YEMEN_NATIONAL' => $original['YEMEN_NATIONAL'] ?? 0,
                        'GENDER' => $original['GENDER'] ?? 'غير محدد',
                        'NATIONALITY_NAME' => $original['NATIONALITY_NAME'] ?? ($original['COUNTRY_NAME'] ?? 'غير محدد'),
                        'UPDATE_BY' => auth()->id(),
                        'NOTES' => 'تم حفظ نسخة تاريخية قبل التحديث من وزارة',
                    ]);
                }

                // Update the applicant record with normalized data
                $record->update($data);
                \Filament\Notifications\Notification::make()->success()->title('تم تحديث بيانات المتقدم بنجاح!')->send();

                // Refresh the form data
                if (method_exists($livewire, 'fillForm')) {
                    $livewire->fillForm();
                }
            });
    }
}
