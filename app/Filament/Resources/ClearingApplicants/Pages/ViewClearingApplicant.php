<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use App\Models\MonitorClearingReviewing;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewClearingApplicant extends ViewRecord
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make(),
        ];

        // First Review Actions
        $actions[] = Actions\Action::make('accept_first_review')
            ->label('اعتماد المراجعة الأولى')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->requiresConfirmation()
            ->visible(fn () => $this->record->STATUS?->value === 'READY' && in_array($this->record->REVIEWED, [0, 2, -1, null]))
            ->action(function () {
                $this->record->update([
                    'REVIEWED' => 1,
                    'REVIEW_BY' => auth()->id(),
                    'REVIEW_ON' => now(),
                    'REJECT_REASON' => null,
                    'FREEZE' => \App\Enums\FreezeStatus::FROZEN,
                ]);

                MonitorClearingReviewing::create([
                    'UNID' => $this->record->UNID,
                    'APPLICANT_IDENT' => $this->record->APPLICANT_IDENT,
                    'REVIEW_RESULTE' => 'ACCEPT',
                    'REVIEW_BY' => auth()->id(),
                    'RECORD_DATE' => now(),
                ]);
            });

        $actions[] = Actions\Action::make('reject_first_review')
            ->label('رفض المراجعة الأولى')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->form([
                Textarea::make('reason')
                    ->label('سبب الرفض')
                    ->required()
            ])
            ->visible(fn () => $this->record->STATUS?->value === 'READY' && in_array($this->record->REVIEWED, [0, 1, -1, null]))
            ->action(function (array $data) {
                $this->record->update([
                    'REVIEWED' => 2,
                    'REVIEW_BY' => auth()->id(),
                    'REVIEW_ON' => now(),
                    'REJECT_REASON' => $data['reason'],
                    'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                ]);

                MonitorClearingReviewing::create([
                    'UNID' => $this->record->UNID,
                    'APPLICANT_IDENT' => $this->record->APPLICANT_IDENT,
                    'REVIEW_RESULTE' => 'REJECT',
                    'REJECT_REASON' => $data['reason'],
                    'REVIEW_BY' => auth()->id(),
                    'RECORD_DATE' => now(),
                ]);
            });

        // Second Review Actions (if configured)
        // Assume config 'legacy_attachments.requires_second_review' is true by default
        if (config('legacy_attachments.requires_second_review', true)) {
            $actions[] = Actions\Action::make('accept_second_review')
                ->label('اعتماد المراجعة الثانية')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->REVIEWED == 1 && in_array($this->record->SECOND_REVIEWED, [0, 2, -1, null]))
                ->action(function () {
                    $this->record->update([
                        'SECOND_REVIEWED' => 1,
                        'SECOND_REVIEWED_BY' => auth()->id(),
                        'SECOND_REVIEWED_ON' => now(),
                        'SECOND_REJECT_REASON' => null,
                    'FREEZE' => \App\Enums\FreezeStatus::FROZEN,
                    ]);

                    MonitorClearingReviewing::create([
                        'UNID' => $this->record->UNID,
                        'APPLICANT_IDENT' => $this->record->APPLICANT_IDENT,
                        'REVIEW_RESULTE' => 'ACCEPT_SECOND',
                        'REVIEW_BY' => auth()->id(),
                        'RECORD_DATE' => now(),
                    ]);
                });

            $actions[] = Actions\Action::make('reject_second_review')
                ->label('رفض المراجعة الثانية')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->form([
                    Textarea::make('reason')
                        ->label('سبب الرفض')
                        ->required()
                ])
                ->visible(fn () => $this->record->REVIEWED == 1 && in_array($this->record->SECOND_REVIEWED, [0, 1, -1, null]))
                ->action(function (array $data) {
                    $this->record->update([
                        'SECOND_REVIEWED' => 2,
                        'SECOND_REVIEWED_BY' => auth()->id(),
                        'SECOND_REVIEWED_ON' => now(),
                        'SECOND_REJECT_REASON' => $data['reason'],
                    'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                    ]);

                    MonitorClearingReviewing::create([
                        'UNID' => $this->record->UNID,
                        'APPLICANT_IDENT' => $this->record->APPLICANT_IDENT,
                        'REVIEW_RESULTE' => 'REJECT_SECOND',
                        'REJECT_REASON' => $data['reason'],
                        'REVIEW_BY' => auth()->id(),
                        'RECORD_DATE' => now(),
                    ]);
                });
        }

        return $actions;
    }
}
