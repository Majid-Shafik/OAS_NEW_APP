<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use App\Filament\Traits\HasCompleteFileAction;
use App\Filament\Traits\HasMinistryRefreshAction;
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
use Illuminate\Support\Facades\Log;



class ViewApplicant extends ViewRecord
{
    use HasCompleteFileAction;
    use HasMinistryRefreshAction;

    protected static string $resource = ApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getApiRefreshAction(),
            $this->getConvertToClearingAction(),
            $this->getCompleteFileAction(),
            EditAction::make()
                ->visible(fn (\App\Models\Applicant $record) => $record->STATUS !== \App\Enums\ApplicantStatus::Ready || auth()->user()->isAdmin()),
        ];
    }

    protected function getConvertToClearingAction(): Action
    {
        return Action::make('convertToClearing')
            ->label('تحويل لطالب مقاصاة')
            ->color('brand')
            ->icon('heroicon-o-arrow-path')
            ->visible(fn(\App\Models\Applicant $record) => auth()->user()->can('ConvertToClearing:Applicant') && $record->IS_CLEARING?->value !== 1)
            ->requiresConfirmation()
            ->modalHeading('تأكيد التحويل')
            ->modalDescription('هل أنت متأكد من تحويل هذا الطالب إلى طالب مقاصاة؟ سيتم فك تجميد الملف لإضافة مرفقات المقاصاة.')
            ->action(function (\App\Models\Applicant $record) {
                $oldValue = $record->IS_CLEARING?->value ?? 0;
                $newValue = 1;
                
                $record->IS_CLEARING = $newValue;
                $record->STATUS = \App\Enums\ApplicantStatus::Updated;
                $record->FREEZE = 0; // Unfrozen
                $record->save();
                
                try {
                    \Illuminate\Support\Facades\DB::connection($record->getConnectionName())
                        ->table('monitor_change_imported')
                        ->insert([
                            'UNID' => $record->UNID,
                            'APPLICANT_IDENT' => $record->APPLICANT_IDENT,
                            'OLD_IMPORTED' => $oldValue,
                            'NEW_IMPORTED' => $newValue,
                            'UPDATE_BY' => auth()->id() ?? 0,
                        ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to log monitor_change_imported: ' . $e->getMessage());
                }
                    
                if (function_exists('activity')) {
                    activity()
                        ->performedOn($record)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'old' => ['IS_CLEARING' => $oldValue],
                            'attributes' => ['IS_CLEARING' => $newValue]
                        ])
                        ->log('تم التحويل إلى طالب مقاصاة');
                }

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title('تم التحويل بنجاح')
                    ->body('تم تغيير نوع الطالب وتم فك تجميد الملف ليصبح تحت التعديل.')
                    ->send();
                    
                // Redirect to Clearing Applicants resource since it's now a clearing applicant
                $this->redirect(\App\Filament\Resources\ClearingApplicants\ClearingApplicantResource::getUrl('view', ['record' => $record]));
            });
    }

    // getCompleteFileAction moved to HasCompleteFileAction trait
}
