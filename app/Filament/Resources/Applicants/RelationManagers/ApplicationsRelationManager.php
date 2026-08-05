<?php

namespace App\Filament\Resources\Applicants\RelationManagers;

use App\Filament\Resources\Applications\ApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                ->schema([

                    Section::make([
                        Select::make('UNID')
                            ->label('الجامعة')
                            ->options(\App\Models\University::coordination()->pluck('U_NAME', 'UNID'))
                            ->default(function ($livewire) {
                                if (session('selected_unid', 0) != 0) {
                                    return session('selected_unid');
                                }
                                if (auth()->user()->UNID != 0) {
                                    return auth()->user()->UNID;
                                }
                                $owner = $livewire->getOwnerRecord();
                                if ($owner && $owner->UNID) {
                                    return $owner->UNID;
                                }
                                return null;
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('FACULTY_IDENT', null);
                                $set('PROGRAM_IDENT', null);
                                $set('STUDYTYPE_IDENT', null);
                                $set('OFFERING_IDENT', null);
                            })
                            ->searchable()
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        Select::make('FACULTY_IDENT')
                            ->label('الكلية')
                            ->options(function ($get) {
                                $unid = $get('UNID');
                                if (!$unid) return [];
                                return \App\Models\Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('PROGRAM_IDENT', null);
                                $set('STUDYTYPE_IDENT', null);
                                $set('OFFERING_IDENT', null);
                            })
                            ->searchable()
                            ->required(),

                        Select::make('PROGRAM_IDENT')
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
                            ->afterStateUpdated(function (Set $set) {
                                $set('STUDYTYPE_IDENT', null);
                                $set('OFFERING_IDENT', null);
                            })
                            ->required(),

                        Select::make('STUDYTYPE_IDENT')
                            ->label('النظام الدراسي')
                            ->options(function ($get) {
                                $unid = $get('UNID');
                                $facultyId = $get('FACULTY_IDENT');
                                $programId = $get('PROGRAM_IDENT');

                                if (!$unid || !$facultyId || !$programId) return [];

                                return \App\Models\Offering::where('UNID', $unid)
                                    ->where('FACULTY_IDENT', $facultyId)
                                    ->where('PROGRAM_IDENT', $programId)
                                    ->where('APPROVAL', 1)
                                    ->whereDate('FROM_DATE', '<=', now())
                                    ->whereDate('TO_DATE', '>=', now())
                                    ->with('studyType')
                                    ->get()
                                    ->pluck('studyType.STUDYTYPE_NAME', 'STUDYTYPE_IDENT')
                                    ->unique();
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('OFFERING_IDENT', null);
                            })
                            ->searchable()
                            ->required(),

                        Select::make('OFFERING_IDENT')
                            ->label('الرغبة')
                            ->options(function ($get) {
                                $unid = $get('UNID');
                                $facultyId = $get('FACULTY_IDENT');
                                $programId = $get('PROGRAM_IDENT');
                                $studyTypeId = $get('STUDYTYPE_IDENT');

                                if (!$unid || !$facultyId || !$programId || !$studyTypeId) return [];

                                return \App\Models\Offering::where('UNID', $unid)
                                    ->where('FACULTY_IDENT', $facultyId)
                                    ->where('PROGRAM_IDENT', $programId)
                                    ->where('STUDYTYPE_IDENT', $studyTypeId)
                                    ->where('APPROVAL', 1)
                                    ->whereDate('FROM_DATE', '<=', now())
                                    ->whereDate('TO_DATE', '>=', now())
                                    ->with('offeringGroup')
                                    ->get()
                                    ->mapWithKeys(function ($offering) {
                                        $secType = \App\Models\ComboValue::getLabel(1, $offering->SEC_SCHOOL_TYPE);
                                        $groupDesc = $offering->offeringGroup ? $offering->offeringGroup->DESCRIPTION : 'رغبة بدون مجموعة';
                                        $label = '[' . $offering->OFFERING_IDENT . '] ' . $groupDesc . ' - ' . $secType;
                                        return [$offering->OFFERING_IDENT => $label];
                                    });
                            })
                            ->searchable()
                            ->required(),

                        TextInput::make('MOBILE_PHONE')
                            ->label('رقم هاتف المتقدم')
                            ->default(fn ($livewire) => $livewire->getOwnerRecord()?->MOBILE_PHONE)
                            ->tel()
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)
                ])
                ->using(function (array $data, string $model): \Illuminate\Database\Eloquent\Model {
                    $applicant = $this->getOwnerRecord();
                    
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

                    return $applicant->applications()->latest('RECORDDATE')->first() ?? new $model();
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
