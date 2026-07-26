<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicant extends CreateRecord
{
    protected static string $resource = ApplicantResource::class;

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
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
