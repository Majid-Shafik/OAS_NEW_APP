<?php

namespace App\Filament\Traits;

use App\Enums\ApplicantStatus;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

trait HasClearingReviewActions
{
    public static function getClearingReviewActions(string $actionClass, string $actionGroupClass): array
    {
        return [
            $actionGroupClass::make([
                $actionClass::make('approveFirst')
                    ->label('المصادقة الأولية')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => $record->IS_CLEARING?->value === 1 && $record->STATUS === ApplicantStatus::Ready && ($record->REVIEWED == 0 || is_null($record->REVIEWED)) && auth()->user()->can('firstReview', $record))
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->update([
                            'REVIEWED' => 1,
                            'REVIEW_BY' => auth()->id(),
                            'REVIEW_ON' => now(),
                            'REJECT_REASON' => null,
                            'FREEZE' => \App\Enums\FreezeStatus::FROZEN,
                        ]);
                        \App\Models\MonitorClearingReviewing::create([
                            'UNID' => $record->UNID,
                            'APPLICANT_IDENT' => $record->APPLICANT_IDENT,
                            'REVIEW_RESULTE' => 'ACCEPT',
                            'REVIEW_BY' => auth()->id(),
                            'RECORD_DATE' => now(),
                        ]);
                        Notification::make()->success()->title('تمت المصادقة الأولى بنجاح')->send();
                    }),
                
                $actionClass::make('rejectFirst')
                    ->label('رفض المراجعة الاولية')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Model $record) => $record->IS_CLEARING?->value === 1 && $record->STATUS === ApplicantStatus::Ready && ($record->REVIEWED == 0 || is_null($record->REVIEWED)) && auth()->user()->can('firstReview', $record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('REJECT_REASON')->label('سبب الرفض')->required(),
                    ])
                    ->action(function (Model $record, array $data) {
                        $record->update([
                            'REVIEWED' => 2,
                            'REJECT_REASON' => $data['REJECT_REASON'],
                            'REVIEW_BY' => auth()->id(),
                            'REVIEW_ON' => now(),
                            'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                        ]);
                        \App\Models\MonitorClearingReviewing::create([
                            'UNID' => $record->UNID,
                            'APPLICANT_IDENT' => $record->APPLICANT_IDENT,
                            'REVIEW_RESULTE' => 'REJECT',
                            'REJECT_REASON' => $data['REJECT_REASON'],
                            'REVIEW_BY' => auth()->id(),
                            'RECORD_DATE' => now(),
                        ]);
                        Notification::make()->danger()->title('تم الرفض من قبل الجامعة')->send();
                    }),

                $actionClass::make('approveSecond')
                    ->label('المصادقة النهائية')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Model $record) => $record->IS_CLEARING?->value === 1 && $record->STATUS === ApplicantStatus::Ready && $record->REVIEWED == 1 && ($record->SECOND_REVIEWED == 0 || is_null($record->SECOND_REVIEWED)) && auth()->user()->can('secondReview', $record))
                    ->requiresConfirmation()
                    ->action(function (Model $record) {
                        $record->update([
                            'SECOND_REVIEWED' => 1,
                            'SECOND_REVIEWED_BY' => auth()->id(),
                            'SECOND_REVIEWED_ON' => now(),
                            'SECOND_REJECT_REASON' => null,
                            'FREEZE' => \App\Enums\FreezeStatus::FROZEN,
                        ]);
                        \App\Models\MonitorClearingReviewing::create([
                            'UNID' => $record->UNID,
                            'APPLICANT_IDENT' => $record->APPLICANT_IDENT,
                            'REVIEW_RESULTE' => 'ACCEPT_SECOND',
                            'REVIEW_BY' => auth()->id(),
                            'RECORD_DATE' => now(),
                        ]);
                        Notification::make()->success()->title('تمت المصادقة الثانية بنجاح')->send();
                    }),

                $actionClass::make('rejectSecond')
                    ->label('رفض المراجعة النهائية')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Model $record) => $record->IS_CLEARING?->value === 1 && $record->STATUS === ApplicantStatus::Ready && $record->REVIEWED == 1 && ($record->SECOND_REVIEWED == 0 || is_null($record->SECOND_REVIEWED)) && auth()->user()->can('secondReview', $record))
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('SECOND_REJECT_REASON')->label('سبب الرفض')->required(),
                    ])
                    ->action(function (Model $record, array $data) {
                        $record->update([
                            'SECOND_REVIEWED' => 2,
                            'SECOND_REJECT_REASON' => $data['SECOND_REJECT_REASON'],
                            'SECOND_REVIEWED_BY' => auth()->id(),
                            'SECOND_REVIEWED_ON' => now(),
                            'FREEZE' => \App\Enums\FreezeStatus::UNFROZEN,
                        ]);
                        \App\Models\MonitorClearingReviewing::create([
                            'UNID' => $record->UNID,
                            'APPLICANT_IDENT' => $record->APPLICANT_IDENT,
                            'REVIEW_RESULTE' => 'REJECT_SECOND',
                            'REJECT_REASON' => $data['SECOND_REJECT_REASON'],
                            'REVIEW_BY' => auth()->id(),
                            'RECORD_DATE' => now(),
                        ]);
                        Notification::make()->danger()->title('تم الرفض من قبل الوزارة')->send();
                    }),
            ])->icon('heroicon-m-ellipsis-vertical')->tooltip('إجراءات المراجعة')
            ->label('خيارات المراجعة'),
        ];
    }
}
