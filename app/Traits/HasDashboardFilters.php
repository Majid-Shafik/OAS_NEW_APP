<?php

namespace App\Traits;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\StudyType;
use App\Models\University;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

trait HasDashboardFilters
{
    public function getDashboardFiltersSchema(): array
    {
        return [
            Section::make()
                ->columns(12)
                ->columnSpanFull()
                ->schema([
                    Select::make('UNID')
                        ->label('الجامعة')
                        ->options(University::Coordination()->pluck('U_NAME', 'UNID'))
                        ->searchable()
                        ->live(),
                    Select::make('FACULTY_IDENT')
                        ->label('الكلية')
                        ->options(
                            fn(Get $get) => Faculty::query()
                                ->when($get('UNID'), fn($q) => $q->where('UNID', $get('UNID')))
                                ->pluck('FACULTY_NAME', 'FACULTY_IDENT')
                        )
                        ->searchable()
                        ->live(),
                    Select::make('PROGRAM_IDENT')
                        ->label('التخصص')
                        ->options(
                            fn(Get $get) => Program::query()
                                ->when($get('UNID'), fn($q) => $q->where('UNID', $get('UNID')))
                                ->when($get('FACULTY_IDENT'), fn($q) => $q->where('FACULTY_IDENT', $get('FACULTY_IDENT')))
                                ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT')
                        )
                        ->searchable()
                        ->live(),
                    Select::make('STUDYTYPE_IDENT')
                        ->label('النظام الدراسي')
                        ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                        ->searchable()
                        ->live(),
                    DatePicker::make('date_from')
                        ->label('من تاريخ'),
                    DatePicker::make('date_to')
                        ->label('إلى تاريخ'),
                ])
                ->columns([
                    'default' => 1,
                    'sm' => 2,
                    'md' => 3,
                    'lg' => 6,
                ]),
        ];
    }
}
