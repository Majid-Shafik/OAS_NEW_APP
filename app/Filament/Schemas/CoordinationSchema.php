<?php

namespace App\Filament\Schemas;

use App\Models\Faculty;
use App\Models\Offering;
use App\Models\Program;
use App\Models\University;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Builder;

class CoordinationSchema
{
    public static function getSchema(): array
    {
        return [
            Fieldset::make('applicationsClearing')
                ->relationship('applicationsClearing')
                ->label('بيانات الجامعة والتخصص التي جاء منها (المقاصاة)')
                ->visible(fn (Get $get, $livewire) => ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : in_array($get('IS_CLEARING'), [1, '1'])) || str_contains(class_basename($livewire), 'Clearing'))
                ->schema([
                    TextInput::make('FROM_COUNTRY_NAME')->label('الدولة القادم منها')->required(),
                    Select::make('FROM_UNIV_IDENT')
                        ->label('الجامعة القادم منها')
                        ->options(fn() => \App\Models\University::all()->mapWithKeys(fn($u) => [$u->UNID => $u->UNID . ' - ' . $u->U_NAME]))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, $state) {
                            $set('FROM_FACULTY_IDENT', null);
                            $set('FROM_FACULTY_NAME', null);
                            $set('FROM_PROGRAM_IDENT', null);
                            $set('FROM_PROGRAM_NAME', null);
                            if ($state) {
                                $u = \App\Models\University::find($state);
                                if ($u) $set('FROM_UNIV_NAME', $u->UNID . ' - ' . $u->U_NAME);
                            } else {
                                $set('FROM_UNIV_NAME', null);
                            }
                        })
                        ->required(),
                    \Filament\Forms\Components\Hidden::make('FROM_UNIV_NAME'),

                    Select::make('FROM_FACULTY_IDENT')
                        ->label('الكلية القادم منها')
                        ->options(function (Get $get) {
                            $unid = $get('FROM_UNIV_IDENT');
                            if (!$unid) return [];
                            return \App\Models\Faculty::where('UNID', $unid)->get()
                                ->mapWithKeys(fn($f) => [$f->FACULTY_IDENT => $f->FACULTY_IDENT . ' - ' . $f->FACULTY_NAME]);
                        })
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, Get $get, $state) {
                            $set('FROM_PROGRAM_IDENT', null);
                            $set('FROM_PROGRAM_NAME', null);
                            if ($state && $get('FROM_UNIV_IDENT')) {
                                $f = \App\Models\Faculty::where('UNID', $get('FROM_UNIV_IDENT'))->where('FACULTY_IDENT', $state)->first();
                                if ($f) $set('FROM_FACULTY_NAME', $f->FACULTY_IDENT . ' - ' . $f->FACULTY_NAME);
                            } else {
                                $set('FROM_FACULTY_NAME', null);
                            }
                        })
                        ->required(),
                    \Filament\Forms\Components\Hidden::make('FROM_FACULTY_NAME'),

                    Select::make('FROM_PROGRAM_IDENT')
                        ->label('التخصص القادم منه')
                        ->options(function (Get $get) {
                            $unid = $get('FROM_UNIV_IDENT');
                            $faculty = $get('FROM_FACULTY_IDENT');
                            if (!$unid || !$faculty) return [];
                            return \App\Models\Program::where('UNID', $unid)->where('FACULTY_IDENT', $faculty)->get()
                                ->mapWithKeys(fn($p) => [$p->PROGRAM_IDENT => $p->PROGRAM_IDENT . ' - ' . $p->PROGRAM_NAME]);
                        })
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, Get $get, $state) {
                            if ($state && $get('FROM_UNIV_IDENT') && $get('FROM_FACULTY_IDENT')) {
                                $p = \App\Models\Program::where('UNID', $get('FROM_UNIV_IDENT'))
                                    ->where('FACULTY_IDENT', $get('FROM_FACULTY_IDENT'))
                                    ->where('PROGRAM_IDENT', $state)->first();
                                if ($p) $set('FROM_PROGRAM_NAME', $p->PROGRAM_IDENT . ' - ' . $p->PROGRAM_NAME);
                            } else {
                                $set('FROM_PROGRAM_NAME', null);
                            }
                        })
                        ->required(),
                    \Filament\Forms\Components\Hidden::make('FROM_PROGRAM_NAME'),
                    TextInput::make('NO_STUDY_YEARS')->label('عدد سنوات الدراسة')->numeric(),
                    TextInput::make('STUDY_LEVEL')->label('مستوى الدراسة')->numeric(),
                    TextInput::make('FROM_YEAR')->label('عام الانضمام')->numeric(),
                    Textarea::make('MOVING_REASON')->label('سبب الانتقال')->required()->columnSpanFull(),
                ])
                ->columns(4)
                ->columnSpanFull(),


            Fieldset::make('applications')
                ->columnSpanFull()
                ->label('التخصص المراد التنسيق فيه')
                ->schema([


                    Select::make('APPLICANT_TYPE')
                        ->label('نوع المتقدم')
                        ->optionsFromConfig('applicant_type')
                        ->live()
                        ->default(1)
                        ->required(),
                    DatePicker::make('ADMITTED_ON')->label('تاريخ القبول')->dehydrated(false),
                    Grid::make(3)->schema([
                        Select::make('UNID')
                            ->label('الجامعة')
                            ->options(function (Get $get) {
                                if ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : $get('IS_CLEARING') == 1) {
                                    return University::clearing()->pluck('U_NAME', 'UNID');
                                }

                                $offerings = self::getFilteredOfferings(
                                    $get('SEC_SCHOOL_TYPE'),
                                    $get('SEC_SCHOOL_RATE'),
                                    $get('SEC_SCHOOL_YEAR'),
                                    $get('YEMEN_NATIONAL')
                                );
                                $unids = $offerings->pluck('UNID')->unique()->toArray();

                                return University::coordination()->whereIn('UNID', $unids)->pluck('U_NAME', 'UNID');
                            })
                            ->visible(fn() => auth()->user()->UNID == 0)
                            ->default(fn() => auth()->user()->UNID != 0 ? auth()->user()->UNID : null)
                            ->dehydrated()
                            ->live()
                            ->searchable()
                            ->required(fn() => auth()->user()->UNID == 0),

                        Select::make('ADMITTED_FACULITY')
                            ->label('الكلية المقبول بها')
                            ->required()
                            ->dehydrated(false)
                            ->options(function (Get $get) {
                                $unid = $get('UNID') ?? (auth()->user()->UNID != 0 ? auth()->user()->UNID : null);
                                if (!$unid) return [];

                                if ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : $get('IS_CLEARING') == 1) {
                                    return Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                                }

                                $offerings = self::getFilteredOfferings(
                                    $get('SEC_SCHOOL_TYPE'),
                                    $get('SEC_SCHOOL_RATE'),
                                    $get('SEC_SCHOOL_YEAR'),
                                    $get('YEMEN_NATIONAL')
                                );
                                $faculties = $offerings->where('UNID', $unid)->pluck('FACULTY_IDENT')->unique()->toArray();

                                return Faculty::where('UNID', $unid)
                                    ->whereIn('FACULTY_IDENT', $faculties)
                                    ->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                            })
                            ->live()
                            ->searchable(),

                        Select::make('ADMITTED_PROGRAM')
                            ->label('التخصص المقبول به')
                            ->required()
                            ->dehydrated(false)
                            ->options(function (Get $get) {
                                $unid = $get('UNID') ?? (auth()->user()->UNID != 0 ? auth()->user()->UNID : null);
                                $facultyId = $get('ADMITTED_FACULITY');

                                if (!$unid || !$facultyId) return [];

                                if ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : $get('IS_CLEARING') == 1) {
                                    return Program::where('UNID', $unid)
                                        ->where('FACULTY_IDENT', $facultyId)
                                        ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                                }

                                $offerings = self::getFilteredOfferings(
                                    $get('SEC_SCHOOL_TYPE'),
                                    $get('SEC_SCHOOL_RATE'),
                                    $get('SEC_SCHOOL_YEAR'),
                                    $get('YEMEN_NATIONAL')
                                );
                                $programs = $offerings->where('UNID', $unid)
                                    ->where('FACULTY_IDENT', $facultyId)
                                    ->pluck('PROGRAM_IDENT')
                                    ->unique()
                                    ->toArray();

                                return Program::where('UNID', $unid)
                                    ->where('FACULTY_IDENT', $facultyId)
                                    ->whereIn('PROGRAM_IDENT', $programs)
                                    ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                            })
                            ->searchable()
                            ->live(),
                    ]),
                    Select::make('ADMITTED_OFFERING')
                        ->label('الرغبة المتاحة')
                        ->required()
                        ->dehydrated(false)
                        ->options(function (Get $get) {
                            $unid = $get('UNID') ?? (auth()->user()->UNID != 0 ? auth()->user()->UNID : null);
                            $facultyId = $get('ADMITTED_FACULITY');
                            $programId = $get('ADMITTED_PROGRAM');

                            if (!$unid || !$facultyId || !$programId) {
                                return [];
                            }

                            if ($get('IS_CLEARING') instanceof \App\Enums\IsClearingType ? $get('IS_CLEARING')->value == 1 : $get('IS_CLEARING') == 1) {
                                return Offering::where('UNID', $unid)
                                    ->where('FACULTY_IDENT', $facultyId)
                                    ->where('PROGRAM_IDENT', $programId)
                                    ->with('studyType')
                                    ->get()
                                    ->mapWithKeys(function ($offering) {
                                        $label = $offering->studyType ? $offering->studyType->STUDYTYPE_NAME : 'رغبة رقم ' . $offering->OFFERING_IDENT;
                                        return [$offering->OFFERING_IDENT => $label];
                                    });
                            }

                            $offerings = self::getFilteredOfferings(
                                $get('SEC_SCHOOL_TYPE'),
                                $get('SEC_SCHOOL_RATE'),
                                $get('SEC_SCHOOL_YEAR'),
                                $get('YEMEN_NATIONAL')
                            );

                            return $offerings->where('UNID', $unid)
                                ->where('FACULTY_IDENT', $facultyId)
                                ->where('PROGRAM_IDENT', $programId)
                                ->with('studyType')
                                ->get()
                                ->mapWithKeys(function ($offering) {
                                    $label = $offering->studyType ? $offering->studyType->STUDYTYPE_NAME : 'رغبة رقم ' . $offering->OFFERING_IDENT;
                                    return [$offering->OFFERING_IDENT => $label];
                                });
                        })
                        ->searchable()
                        ->live(),
                ])


        ];
    }

    public static function getFilteredOfferings($secType, $secRate, $secYear, $isYemeni): Builder
    {
        $query = Offering::query()
            ->where('APPROVAL', 1)
            ->whereDate('FROM_DATE', '<=', now())
            ->whereDate('TO_DATE', '>=', now());

        // فلترة حسب نوع الثانوية
        if ($secType) {
            $query->where('SEC_SCHOOL_TYPE', $secType);
        }

        // فلترة حسب المعدل
        if ($secRate) {
            $query->where('SEC_SCHOOL_ACCEPT_RATE', '<=', $secRate);
        }

        // فلترة حسب عمر الثانوية
        if ($secYear) {
            // اعتماد السنة الحالية كعام التنسيق
            $currentYear = (int) date('Y');
            $age = $currentYear - (int)$secYear;

            if ($isYemeni) {
                $query->where(function ($q) use ($age) {
                    $q->whereNull('Y_SEC_SCHOOL_MAX_AGE')
                        ->orWhere('Y_SEC_SCHOOL_MAX_AGE', '>=', $age);
                });
            } else {
                $query->where(function ($q) use ($age) {
                    $q->whereNull('NY_SEC_SCHOOL_MAX_AGE')
                        ->orWhere('NY_SEC_SCHOOL_MAX_AGE', '>=', $age);
                });
            }
        }

        return $query;
    }
}
