<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use App\Models\MonitorClearingReviewing;
use App\Filament\Traits\HasClearingReviewActions;
use App\Filament\Traits\HasCompleteFileAction;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;

class ViewClearingApplicant extends ViewRecord
{
    protected static string $resource = ClearingApplicantResource::class;

    use HasClearingReviewActions, HasCompleteFileAction;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make(),
            $this->getConvertToApplicantAction(),
            $this->getCompleteFileAction(),
        ];

        $reviewActions = self::getClearingReviewActions(
            Actions\Action::class,
            \Filament\Actions\ActionGroup::class
        );

        return array_merge($actions, $reviewActions);
    }

    protected function getConvertToApplicantAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('convertToApplicant')
            ->label('تحويل لطالب اعتيادي')
            ->color('brand')
            ->icon('heroicon-o-check-circle')
            ->visible(fn() => auth()->user()->can('ConvertToApplicant:ClearingApplicant') && $this->record->IS_CLEARING?->value === 1)
            ->requiresConfirmation()
            ->modalHeading('تأكيد التحويل')
            ->modalDescription('هل أنت متأكد من تحويل هذا الطالب إلى اعتيادي؟ سيتم فك تجميد الملف وإرجاع الحالة إلى تحت التعديل.')
            ->action(function () {
                $oldValue = $this->record->IS_CLEARING?->value ?? 0;
                $newValue = 0;
                
                $this->record->IS_CLEARING = $newValue;
                $this->record->STATUS = \App\Enums\ApplicantStatus::Updated;
                $this->record->FREEZE = 0; // Unfrozen
                $this->record->save();
                
                try {
                    \Illuminate\Support\Facades\DB::connection($this->record->getConnectionName())
                        ->table('monitor_change_imported')
                        ->insert([
                            'UNID' => $this->record->UNID,
                            'APPLICANT_IDENT' => $this->record->APPLICANT_IDENT,
                            'OLD_IMPORTED' => $oldValue,
                            'NEW_IMPORTED' => $newValue,
                            'UPDATE_BY' => auth()->id() ?? 0,
                        ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to log monitor_change_imported: ' . $e->getMessage());
                }
                    
                if (function_exists('activity')) {
                    activity()
                        ->performedOn($this->record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'old' => ['IS_CLEARING' => $oldValue],
                            'attributes' => ['IS_CLEARING' => $newValue]
                        ])
                        ->log('تم التحويل إلى طالب اعتيادي');
                }

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('تم التحويل بنجاح')
                    ->body('تم تغيير نوع الطالب وتم فك تجميد الملف ليصبح تحت التعديل.')
                    ->send();
                    
                // Redirect to Normal Applicants resource since it's now a normal applicant
                $this->redirect(\App\Filament\Resources\Applicants\ApplicantResource::getUrl('view', ['record' => $this->record]));
            });
    }
}
