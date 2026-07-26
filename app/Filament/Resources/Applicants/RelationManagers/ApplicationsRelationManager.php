<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
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
                ->form([
                    \Filament\Forms\Components\Select::make('UNID')
                        ->label('الجامعة')
                        ->options(\App\Models\University::coordination()->pluck('U_NAME', 'UNID'))
                        ->live()
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('FACULTY_IDENT')
                        ->label('الكلية')
                        ->options(function ($get) {
                            $unid = $get('UNID');
                            if (!$unid) return [];
                            return \App\Models\Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                        })
                        ->live()
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\Select::make('PROGRAM_IDENT')
                        ->label('التخصص')
                        ->options(function ($get) {
                            $unid = $get('UNID');
                            $facultyId = $get('FACULTY_IDENT');
                            if (!$unid || !$facultyId) return [];
                            return \App\Models\Program::where('UNID', $unid)
                                ->where('FACULTY_IDENT', $facultyId)
                                ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                        })
                        ->searchable()
                        ->live()
                        ->required(),

                    \Filament\Forms\Components\Select::make('OFFERING_IDENT')
                        ->label('الرغبة المتاحة')
                        ->options(function ($get) {
                            $unid = $get('UNID');
                            $facultyId = $get('FACULTY_IDENT');
                            $programId = $get('PROGRAM_IDENT');

                            if (!$unid || !$facultyId || !$programId) return [];

                            return \App\Models\Offering::where('UNID', $unid)
                                ->where('FACULTY_IDENT', $facultyId)
                                ->where('PROGRAM_IDENT', $programId)
                                ->where('APPROVAL', 1)
                                ->with('studyType')
                                ->get()
                                ->mapWithKeys(function ($offering) {
                                    $label = $offering->studyType ? $offering->studyType->STUDYTYPE_NAME : 'رغبة رقم ' . $offering->OFFERING_IDENT;
                                    return [$offering->OFFERING_IDENT => $label];
                                });
                        })
                        ->searchable()
                        ->required(),
                ])
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $applicant = $this->getOwnerRecord();
                    $offeringIdent = $data['OFFERING_IDENT'];
                    $isClearing = $applicant->IS_CLEARING === \App\Enums\IsClearingType::CLEARING;
                    $imported = $applicant->IMPORTED ?? 2;

                    $service = app(\App\Services\ApplicantRegistrationService::class);
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

                    return $applicant->applications()->latest('RECORDDATE')->first() ?? new $model();
                }),
        ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return ApplicationResource::infolist($schema);
    }
}

