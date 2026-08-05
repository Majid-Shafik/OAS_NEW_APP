<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicant extends CreateRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
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
                ->title('لا يمكن إكمال التنسيق')
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
                    $typeB->SEC_SCHOOL_CERTIFICATE = $filename; // store only filename as TypeB expects
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
                $existing->update([
                    'EMAIL' => $data['EMAIL'] ?? $existing->EMAIL,
                    'MOBILE_PHONE' => $data['MOBILE_PHONE'] ?? $existing->MOBILE_PHONE,
                    'NATIONAL_NUMBER' => $data['NATIONAL_NUMBER'] ?? $existing->NATIONAL_NUMBER,
                    'IDENT_NO' => $data['IDENT_NO'] ?? $existing->IDENT_NO,
                    'BLOOD_GROUP' => $data['BLOOD_GROUP'] ?? $existing->BLOOD_GROUP,
                ]);
                return $existing;
            }
        }

        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        $applicant = $this->record;

        $offeringIdent = $this->data['ADMITTED_OFFERING'] ?? null;
        $isClearing = $this->data['IS_CLEARING'] ?? \App\Enums\IsClearingType::NORMAL->value;
        if ($isClearing instanceof \App\Enums\IsClearingType) {
            $isClearing = $isClearing->value;
        }
        $imported = $this->data['IMPORTED'] ?? 2;

        if ($offeringIdent) {
            try {
                $service = app(\App\Services\ApplicantRegistrationService::class);
                $result = $service->registerApplications($applicant, [$offeringIdent], $isClearing == 1, $imported);

                if (empty($result['failed'])) {
                    \Filament\Notifications\Notification::make()
                        ->title('تم إضافة رغبة التنسيق بنجاح')
                        ->success()
                        ->send();
                } else {
                    $reasons = collect($result['failed'])->pluck('reason')->unique()->join(', ');
                    \Filament\Notifications\Notification::make()
                        ->title('تم حفظ المتقدم ولكن تعذر تسجيل الرغبة')
                        ->body('السبب: ' . $reasons)
                        ->warning()
                        ->send();
                }
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('تم حفظ المتقدم مع حدوث خطأ في عملية التنسيق')
                    ->body($e->getMessage())
                    ->danger()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->record->getProfileUrl();
    }

    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->hidden(fn () => $this->data['applicant_exists'] ?? false);
    }

    protected function getCreateAnotherFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden(fn () => $this->data['applicant_exists'] ?? false);
    }
}
