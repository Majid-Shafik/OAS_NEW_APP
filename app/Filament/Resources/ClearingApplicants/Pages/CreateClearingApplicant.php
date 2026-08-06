<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClearingApplicant extends CreateRecord
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $data['IS_CLEARING'] = \App\Enums\IsClearingType::CLEARING->value;
        $data['IMPORTED'] = $data['IMPORTED'] ?? 2;
        $data['APPLICANT_TYPE'] = $data['APPLICANT_TYPE'] ?? 1;

        if (empty($data['UNID'])) {
            $data['UNID'] = $this->data['UNID'] ?? session('selected_unid') ?? (auth()->user()->UNID != 0 ? auth()->user()->UNID : 1);
        }

        if (empty($data['COUNTRY_IDENT']) && !empty($data['COUNTRY_NAME'])) {
            $data['COUNTRY_IDENT'] = \App\Models\Country::where('COUNTRY_NAME', $data['COUNTRY_NAME'])->value('COUNTRY_IDENT');
            if (empty($data['COUNTRY_IDENT']) && ($data['YEMEN_NATIONAL'] ?? 0) == 1) {
                $data['COUNTRY_IDENT'] = 242;
            }
        }

        if (!empty($data['hs_degree_not_approved'])) {
            \Filament\Notifications\Notification::make()
                ->warning()
                ->title('لا يمكن إكمال المقاصاة')
                ->body('بيانات الشهادة من النوع B قيد المراجعة، يرجى اعتمادها أولاً.')
                ->send();
            throw new \Filament\Support\Exceptions\Halt();
        }

        if (!empty($data['is_not_found'])) {
            // This is a manual entry (Type B)
            $typeB = new \App\Models\HighSchoolDegreeBType();
            $typeB->STUDENT_NAME = $data['FULL_NAME'] ?? '';
            $typeB->SEC_SCHOOL_YEAR = $data['SEC_SCHOOL_YEAR'] ?? '';
            $typeB->SEC_SCHOOL_SEATNO = $data['SEC_SCHOOL_SEATNO'] ?? '';
            $typeB->UNID = $data['UNID'] ?? 1;
            $typeB->SEC_SCHOOL_OVERALLMARK = $data['SEC_SCHOOL_OVERALLMARK'] ?? 800;
            $typeB->SEC_SCHOOL_MARK = $data['SEC_SCHOOL_MARK'] ?? 0;
            $typeB->SEC_SCHOOL_RATE = $data['SEC_SCHOOL_RATE'] ?? 0;
            $typeB->SEC_SCHOOL_TYPE = $data['SEC_SCHOOL_TYPE'] ?? '';
            $typeB->SEC_SCHOOL_NAME = $data['SEC_SCHOOL_NAME'] ?? '';
            $typeB->SEC_SCHOOL_PLACE = $data['SEC_SCHOOL_PLACE'] ?? '';
            $typeB->SEC_SCHOOL_PROVINCE = $data['SEC_SCHOOL_PROVINCE'] ?? '';
            $typeB->SEC_SCHOOL_TERRITORY = $data['SEC_SCHOOL_TERRITORY'] ?? '';
            $typeB->GENDER = $data['GENDER'] ?? '';
            $typeB->DATE_OF_BIRTH = $data['DATE_OF_BIRTH'] ?? null;
            $typeB->PLACE_OF_BIRTH = $data['PLACE_OF_BIRTH'] ?? '';
            $typeB->PROVINCE = $data['PROVINCE'] ?? '';
            $typeB->TERRITORY = $data['TERRITORY'] ?? '';
            $typeB->COUNTRY_NAME = $data['COUNTRY_NAME'] ?? '';
            $typeB->YEMEN_NATIONAL = $data['YEMEN_NATIONAL'] ?? 1;
            $typeB->EMAIL = $data['EMAIL'] ?? '';
            $typeB->MOBILE_PHONE = $data['MOBILE_PHONE'] ?? '';
            $typeB->APPROVED = 0;
            $typeB->RECORDDATE = now();
            $typeB->INSERTED_BY = auth()->id();
            
            // Handle file upload
            if (!empty($data['secondary_certificate'])) {
                $file = is_array($data['secondary_certificate']) ? reset($data['secondary_certificate']) : $data['secondary_certificate'];
                
                $portalPrefix = \App\Helpers\PortalHelper::getPortalPrefix();
                $path = "uploads/{$portalPrefix}/images/attachments/secondary";
                
                if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                    $filename = \Illuminate\Support\Str::random(15) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs($path, $filename, config('legacy_attachments.disk', 'public'));
                    $typeB->SEC_SCHOOL_CERTIFICATE = $filename;
                } else if (is_string($file)) {
                    $typeB->SEC_SCHOOL_CERTIFICATE = basename($file);
                }
            }

            $typeB->save();

            \Filament\Notifications\Notification::make()
                ->success()
                ->title('تم إضافة بيانات الطالب بنجاح')
                ->body('تم حفظ بيانات الطالب كشهادة ثانوية نوع B لغرض المراجعة والاعتماد.')
                ->send();

            $this->redirect(\App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource::getUrl('view', ['record' => $typeB->getKey()]));
            
            throw new \Filament\Support\Exceptions\Halt();
        }

        if (!empty($data['applicant_exists']) && !empty($data['applicant_id'])) {
            $existing = \App\Models\Applicant::find($data['applicant_id']);
            if ($existing) {
                $confirmedApp = \App\Models\Application::where('UNID', $existing->UNID)
                    ->where('APPLICANT_IDENT', $existing->APPLICANT_IDENT)
                    ->where('CONFIRMED_BY_APPLICANT', 1)
                    ->with('program')
                    ->first();
                $isConfirmed = $confirmedApp !== null || (!empty($existing->ADMITTED_OFFERING) && $existing->ADMITTED_OFFERING > 0);
                if ($isConfirmed) {
                    $progName = $confirmedApp?->program?->PROGRAM_NAME ?? '';
                    \Filament\Notifications\Notification::make()
                        ->warning()
                        ->title('لا يمكن إكمال العملية')
                        ->body('المتقدم مؤكد في تخصص' . ($progName ? " ({$progName})" : '') . '، يجب إلغاء تأكيده في ذلك التخصص أولاً حتى يستطيع إضافة رغبة جديدة.')
                        ->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                $existing->update([
                    'EMAIL' => $data['EMAIL'] ?? $existing->EMAIL,
                    'MOBILE_PHONE' => $data['MOBILE_PHONE'] ?? $existing->MOBILE_PHONE,
                    'NATIONAL_NUMBER' => $data['NATIONAL_NUMBER'] ?? $existing->NATIONAL_NUMBER,
                    'IDENT_NO' => $data['IDENT_NO'] ?? $existing->IDENT_NO,
                    'BLOOD_GROUP' => $data['BLOOD_GROUP'] ?? $existing->BLOOD_GROUP,
                    'IS_CLEARING' => \App\Enums\IsClearingType::CLEARING->value,
                ]);
                return $existing;
            }
        }

        // إزالة أي حقول تبدأ بـ ADMITTED_ لمنع حفظها أثناء الإضافة حيث يتم تعبئتها وقت تأكيد القبول
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'ADMITTED_')) {
                unset($data[$key]);
            }
        }

        return static::getModel()::create($data);
    }

    protected ?\Filament\Notifications\Notification $customCreatedNotification = null;

    protected function afterCreate(): void
    {
        $applicant = $this->record;
        $offeringIdent = $this->data['OFFERING_IDENT'] ?? $this->data['ADMITTED_OFFERING'] ?? null;
        $imported = $this->data['IMPORTED'] ?? 2;

        if ($offeringIdent) {
            try {
                $service = app(\App\Services\ApplicantRegistrationService::class);
                $result = $service->registerApplications($applicant, [$offeringIdent], true, $imported);

                if (empty($result['failed'])) {
                    $this->customCreatedNotification = \Filament\Notifications\Notification::make()
                        ->title('تم إضافة المتقدم ورغبة المقاصاة بنجاح')
                        ->success();
                } else {
                    $reasons = collect($result['failed'])->pluck('reason')->unique()->join(', ');
                    session()->flash('offering_registration_failed', [
                        'reasons' => $reasons,
                        'offering_ident' => $offeringIdent,
                    ]);
                    $this->customCreatedNotification = \Filament\Notifications\Notification::make()
                        ->title('تم حفظ بيانات المتقدم بنجاح')
                        ->body("ولكن تعذر تسجيل رغبة المقاصاة للأسباب التالية: {$reasons}")
                        ->warning()
                        ->persistent();
                }
            } catch (\Exception $e) {
                session()->flash('offering_registration_failed', [
                    'reasons' => $e->getMessage(),
                    'offering_ident' => $offeringIdent,
                ]);
                $this->customCreatedNotification = \Filament\Notifications\Notification::make()
                    ->title('تم حفظ بيانات المتقدم')
                    ->body("حدث خطأ أثناء تسجيل رغبة المقاصاة: {$e->getMessage()}")
                    ->danger()
                    ->persistent();
            }
        }
    }

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        return $this->customCreatedNotification ?? parent::getCreatedNotification();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
