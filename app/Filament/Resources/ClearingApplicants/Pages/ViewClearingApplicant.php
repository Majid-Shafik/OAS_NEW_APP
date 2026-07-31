<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use App\Models\MonitorClearingReviewing;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;

class ViewClearingApplicant extends ViewRecord
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\EditAction::make(),
        ];

        // First Review Actions
        $actions[] = Actions\Action::make('first_review')
            ->label('المراجعة الأولى')
            ->color(fn () => $this->record->REVIEWED == 1 ? 'success' : ($this->record->REVIEWED == 2 ? 'danger' : 'warning'))
            ->icon('heroicon-o-clipboard-document-check')
            ->schema([
                Select::make('result')
                    ->label('نتيجة المراجعة')
                    ->options([
                        'accept' => 'اعتماد المراجعة',
                        'reject' => 'رفض الاستمارة',
                    ])
                    ->required()
                    ->live(),
                Textarea::make('reason')
                    ->label('سبب الرفض')
                    ->required()
                    ->visible(fn (Get $get) => $get('result') === 'reject')
            ])
            ->visible(fn () => $this->record->STATUS?->value === 'READY' && auth()->user()->can('firstReview', $this->record))
            ->action(function (array $data) {
                if ($data['result'] === 'accept') {
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
                } else {
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
                }
            });

        // Second Review Actions (if configured)
        // Assume config 'legacy_attachments.requires_second_review' is true by default
        if (config('legacy_attachments.requires_second_review', true)) {
            $actions[] = Actions\Action::make('second_review')
                ->label('المراجعة الثانية')
                ->color(fn () => $this->record->SECOND_REVIEWED == 1 ? 'success' : ($this->record->SECOND_REVIEWED == 2 ? 'danger' : 'warning'))
                ->icon('heroicon-o-clipboard-document-check')
                ->schema([
                    Select::make('result')
                        ->label('نتيجة المراجعة')
                        ->options([
                            'accept' => 'اعتماد المراجعة',
                            'reject' => 'رفض الاستمارة',
                        ])
                        ->required()
                        ->live(),
                    Textarea::make('reason')
                        ->label('سبب الرفض')
                        ->required()
                        ->visible(fn (Get $get) => $get('result') === 'reject')
                ])
                ->visible(fn () => $this->record->REVIEWED == 1 && auth()->user()->can('secondReview', $this->record))
                ->action(function (array $data) {
                    if ($data['result'] === 'accept') {
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
                    } else {
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
                    }
                });
        }

        return $actions;
    }
}
