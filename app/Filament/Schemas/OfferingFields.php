<?php

namespace App\Filament\Schemas;

use App\Models\ComboValue;
use App\Models\Faculty;
use App\Models\Offering;
use App\Models\Program;
use App\Models\University;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;

class OfferingFields
{
    /**
     * Get cascading select fields for Faculty, Program, Study Type, and Offering.
     *
     * @param bool $includeUniversity Whether to include the University select field
     * @param bool $dehydrated Whether fields should be dehydrated
     * @return array
     */
    public static function get(bool $includeUniversity = false, bool $dehydrated = false): array
    {
        $fields = [];

        if ($includeUniversity) {
            $fields[] = Select::make('UNID')
                ->label('الجامعة')
                ->options(fn() => University::coordination()->pluck('U_NAME', 'UNID'))
                ->default(function ($livewire = null) {
                    if (session('selected_unid', 0) != 0) {
                        return session('selected_unid');
                    }
                    if (auth()->check() && auth()->user()->UNID != 0) {
                        return auth()->user()->UNID;
                    }
                    if ($livewire && method_exists($livewire, 'getOwnerRecord')) {
                        $owner = $livewire->getOwnerRecord();
                        if ($owner && $owner->UNID) {
                            return $owner->UNID;
                        }
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
                ->required();
        }

        $fields[] = Select::make('FACULTY_IDENT')
            ->label('الكلية')
            ->options(function (Get $get, $livewire = null) {
                $unid = $get('UNID');
                if (!$unid && $livewire && method_exists($livewire, 'getOwnerRecord')) {
                    $unid = $livewire->getOwnerRecord()?->UNID;
                }
                $unid = $unid ?? session('selected_unid') ?? (auth()->check() && auth()->user()->UNID != 0 ? auth()->user()->UNID : 1);
                if (!$unid) return [];
                return Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
            })
            ->live()
            ->afterStateUpdated(function (Set $set) {
                $set('PROGRAM_IDENT', null);
                $set('STUDYTYPE_IDENT', null);
                $set('OFFERING_IDENT', null);
            })
            ->searchable()
            ->required()
            ->dehydrated($dehydrated);

        $fields[] = Select::make('PROGRAM_IDENT')
            ->label('التخصص')
            ->options(function (Get $get, $livewire = null) {
                $unid = $get('UNID');
                if (!$unid && $livewire && method_exists($livewire, 'getOwnerRecord')) {
                    $unid = $livewire->getOwnerRecord()?->UNID;
                }
                $unid = $unid ?? session('selected_unid') ?? (auth()->check() && auth()->user()->UNID != 0 ? auth()->user()->UNID : 1);
                $facultyId = $get('FACULTY_IDENT');
                if (!$unid || !$facultyId) return [];
                return Program::where('UNID', $unid)
                    ->where('FACULTY_IDENT', $facultyId)
                    ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
            })
            ->searchable()
            ->live()
            ->afterStateUpdated(function (Set $set) {
                $set('STUDYTYPE_IDENT', null);
                $set('OFFERING_IDENT', null);
            })
            ->required()
            ->dehydrated($dehydrated);

        $fields[] = Select::make('STUDYTYPE_IDENT')
            ->label('النظام الدراسي')
            ->options(function (Get $get, $livewire = null) {
                $unid = $get('UNID');
                if (!$unid && $livewire && method_exists($livewire, 'getOwnerRecord')) {
                    $unid = $livewire->getOwnerRecord()?->UNID;
                }
                $unid = $unid ?? session('selected_unid') ?? (auth()->check() && auth()->user()->UNID != 0 ? auth()->user()->UNID : 1);
                $facultyId = $get('FACULTY_IDENT');
                $programId = $get('PROGRAM_IDENT');

                if (!$unid || !$facultyId || !$programId) return [];

                return Offering::where('UNID', $unid)
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
            ->required()
            ->dehydrated($dehydrated);

        $fields[] = Select::make('OFFERING_IDENT')
            ->label('الرغبة')
            ->options(function (Get $get, $livewire = null) {
                $unid = $get('UNID');
                if (!$unid && $livewire && method_exists($livewire, 'getOwnerRecord')) {
                    $unid = $livewire->getOwnerRecord()?->UNID;
                }
                $unid = $unid ?? session('selected_unid') ?? (auth()->check() && auth()->user()->UNID != 0 ? auth()->user()->UNID : 1);
                $facultyId = $get('FACULTY_IDENT');
                $programId = $get('PROGRAM_IDENT');
                $studyTypeId = $get('STUDYTYPE_IDENT');

                if (!$unid || !$facultyId || !$programId || !$studyTypeId) return [];

                return Offering::where('UNID', $unid)
                    ->where('FACULTY_IDENT', $facultyId)
                    ->where('PROGRAM_IDENT', $programId)
                    ->where('STUDYTYPE_IDENT', $studyTypeId)
                    ->where('APPROVAL', 1)
                    ->whereDate('FROM_DATE', '<=', now())
                    ->whereDate('TO_DATE', '>=', now())
                    ->with('offeringGroup')
                    ->get()
                    ->mapWithKeys(function ($offering) {
                        $secType = ComboValue::getLabel(1, $offering->SEC_SCHOOL_TYPE);
                        $groupDesc = $offering->offeringGroup ? $offering->offeringGroup->DESCRIPTION : 'رغبة بدون مجموعة';
                        $label = '[' . $offering->OFFERING_IDENT . '] ' . $groupDesc . ' - ' . $secType;
                        return [$offering->OFFERING_IDENT => $label];
                    });
            })
            ->live()
            ->helperText(function (Get $get, $state, $livewire = null) {
                if (!$state) return null;
                $offering = Offering::find($state);
                if (!$offering) return null;

                $rate = $get('SEC_SCHOOL_RATE');
                if ($rate === null && $livewire && method_exists($livewire, 'getOwnerRecord')) {
                    $rate = $livewire->getOwnerRecord()?->SEC_SCHOOL_RATE;
                }

                $acceptRate = (float) ($offering->SEC_SCHOOL_ACCEPT_RATE ?? 0);
                if ($rate !== null && $rate !== '') {
                    $studentRate = (float) $rate;
                    if ($studentRate < $acceptRate) {
                        return new HtmlString("<span style=\"color: #dc2626; font-weight: bold; font-size: 0.875rem;\">⚠️ غير مقبول: معدل الطالب ({$studentRate}%) أقل من الحد الأدنى المطلوب للقبول ({$acceptRate}%)</span>");
                    }
                    return new HtmlString("<span style=\"color: #16a34a; font-weight: bold; font-size: 0.875rem;\">✓ مقبول: معدل القبول المطلوب: {$acceptRate}% (معدل الطالب: {$studentRate}%)</span>");
                }

                return $acceptRate > 0 ? new HtmlString("<span style=\"color: #6b7280; font-weight: 500;\">الحد الأدنى للمعدل المطلوب: {$acceptRate}%</span>") : null;
            })
            ->searchable()
            ->required()
            ->dehydrated($dehydrated);

        return $fields;
    }
}
