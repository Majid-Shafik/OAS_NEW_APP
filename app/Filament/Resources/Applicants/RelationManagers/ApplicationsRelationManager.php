<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Schemas\OfferingFields;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'طلبات التقديم';

    public function form(Schema $schema): Schema
    {
        return ApplicationResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ApplicationResource::table($table)->headerActions([
            CreateAction::make()
                ->label('إضافة رغبة جديدة')
                ->before(function (CreateAction $action) {
                    $applicant = $this->getOwnerRecord();
                    if ($applicant) {
                        $confirmedApp = \App\Models\Application::where('UNID', $applicant->UNID)
                            ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                            ->where('CONFIRMED_BY_APPLICANT', 1)
                            ->with('program')
                            ->first();

                        if ($confirmedApp || (!empty($applicant->ADMITTED_OFFERING) && $applicant->ADMITTED_OFFERING > 0)) {
                            $progName = $confirmedApp?->program?->PROGRAM_NAME ?? '';
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('لا يمكن إضافة رغبة جديدة')
                                ->body('المتقدم مؤكد في تخصص' . ($progName ? " ({$progName})" : '') . '، يجب إلغاء تأكيده في ذلك التخصص أولاً حتى يستطيع إضافة رغبة جديدة.')
                                ->send();
                            $action->halt();
                        }
                    }
                })
                ->schema([
                    Callout::make('تنبيه')
                        ->description(function () {
                            $applicant = $this->getOwnerRecord();
                            $confirmedApp = $applicant ? \App\Models\Application::where('UNID', $applicant->UNID)
                                ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                                ->where('CONFIRMED_BY_APPLICANT', 1)
                                ->with('program')
                                ->first() : null;
                            $progName = $confirmedApp?->program?->PROGRAM_NAME ?? '';
                            return 'المتقدم مؤكد في تخصص' . ($progName ? " ({$progName})" : '') . '، يجب إلغاء تأكيده في ذلك التخصص أولاً حتى يستطيع إضافة رغبة جديدة.';
                        })
                        ->danger()
                        ->visible(function () {
                            $applicant = $this->getOwnerRecord();
                            if (!$applicant) return false;
                            return \App\Models\Application::where('UNID', $applicant->UNID)
                                ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                                ->where('CONFIRMED_BY_APPLICANT', 1)
                                ->exists() || (!empty($applicant->ADMITTED_OFFERING) && $applicant->ADMITTED_OFFERING > 0);
                        })
                        ->columnSpanFull(),

                    Section::make(array_merge(
                        OfferingFields::get(includeUniversity: true, dehydrated: true),
                        [
                            TextInput::make('MOBILE_PHONE')
                                ->label('رقم هاتف المتقدم')
                                ->default(fn () => $this->getOwnerRecord()?->MOBILE_PHONE)
                                ->tel()
                                ->required()
                                ->columnSpanFull(),
                        ]
                    ))->columns(2)
                ])
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $applicant = $this->getOwnerRecord();

                    $confirmedApp = \App\Models\Application::where('UNID', $applicant->UNID)
                        ->where('APPLICANT_IDENT', $applicant->APPLICANT_IDENT)
                        ->where('CONFIRMED_BY_APPLICANT', 1)
                        ->with('program')
                        ->first();
                    if ($confirmedApp || (!empty($applicant->ADMITTED_OFFERING) && $applicant->ADMITTED_OFFERING > 0)) {
                        $progName = $confirmedApp?->program?->PROGRAM_NAME ?? '';
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('لا يمكن إضافة رغبة جديدة')
                            ->body('المتقدم مؤكد في تخصص' . ($progName ? " ({$progName})" : '') . '، يجب إلغاء تأكيده في ذلك التخصص أولاً حتى يستطيع إضافة رغبة جديدة.')
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }

                    if (!empty($data['MOBILE_PHONE']) && $applicant->MOBILE_PHONE !== $data['MOBILE_PHONE']) {
                        $applicant->MOBILE_PHONE = $data['MOBILE_PHONE'];
                        $applicant->save();
                    }

                    $offeringIdent = $data['OFFERING_IDENT'];
                    $isClearing = $applicant->IS_CLEARING === \App\Enums\IsClearingType::CLEARING;
                    $imported = $applicant->IMPORTED ?? 2;

                    $service = app(\App\Services\ApplicantRegistrationService::class);
                    
                    try {
                        $result = $service->registerApplications($applicant, [$offeringIdent], $isClearing, $imported);

                        if (!empty($result['failed'])) {
                            $reasons = collect($result['failed'])->pluck('reason')->unique()->join(', ');
                            \Filament\Notifications\Notification::make()
                                ->title('تعذر تسجيل الرغبة')
                                ->body('السبب: ' . $reasons)
                                ->danger()
                                ->send();
                            throw new \Filament\Support\Exceptions\Halt();
                        }
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('خطأ أثناء العملية')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                        throw new \Filament\Support\Exceptions\Halt();
                    }

                    $applicant->syncStatusAfterApplicationChange();

                    return $applicant->applications()->latest('RECORDDATE')->first() ?? new $model();
                })
                ->after(function () {
                    $applicant = $this->getOwnerRecord();
                    if ($applicant && method_exists($applicant, 'getProfileUrl')) {
                        $this->redirect($applicant->getProfileUrl());
                    }
                }),
        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return ApplicationResource::infolist($schema);
    }

    public function isReadOnly(): bool
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            return false; // Not read-only for admin or owner
        }

        return true; // Read-only for everyone else
    }
}
