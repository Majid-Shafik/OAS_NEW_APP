<?php

namespace App\Filament\Filters;

use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AcademicFilter
{
    /**
     * Create a reusable filter for University, Faculty, and Program.
     *
     * @param  string  $name  The name of the filter.
     * @param  string  $facultyColumn  The database column name for faculty (e.g., FACULTY_IDENT, ADMITTED_FACULITY).
     * @param  string  $programColumn  The database column name for program (e.g., PROGRAM_IDENT, ADMITTED_PROGRAM).
     */
    public static function make(
        string $name = 'university_faculty_program',
        string $facultyColumn = 'FACULTY_IDENT',
        string $programColumn = 'PROGRAM_IDENT'
    ): Filter {
        return Filter::make($name)
            ->form([
                Select::make('UNID')
                    ->label('الجامعة')
                    ->options(University::pluck('U_NAME', 'UNID')->prepend('غير محدد', 0))
                    ->live()
                    ->searchable(),
                Select::make($facultyColumn)
                    ->label('الكلية')
                    ->options(fn (Get $get) => Faculty::where('UNID', $get('UNID'))->pluck('FACULTY_NAME', 'FACULTY_IDENT')->prepend('غير محدد', 0))
                    ->live()
                    ->searchable(),
                Select::make($programColumn)
                    ->label('التخصص')
                    ->options(fn (Get $get) => Program::where('UNID', $get('UNID'))
                        ->where('FACULTY_IDENT', $get($facultyColumn))
                        ->pluck('PROGRAM_NAME', 'PROGRAM_IDENT')->prepend('غير محدد', 0))
                    ->searchable(),
            ])
            ->query(function (Builder $query, array $data) use ($facultyColumn, $programColumn): Builder {
                return $query
                    ->when(
                        isset($data['UNID']) && $data['UNID'] !== '',
                        fn (Builder $query): Builder => $query->where('UNID', $data['UNID'])
                    )
                    ->when(
                        isset($data[$facultyColumn]) && $data[$facultyColumn] !== '',
                        fn (Builder $query): Builder => $query->where($facultyColumn, $data[$facultyColumn])
                    )
                    ->when(
                        isset($data[$programColumn]) && $data[$programColumn] !== '',
                        fn (Builder $query): Builder => $query->where($programColumn, $data[$programColumn])
                    );
            })
            ->columns(3)
            ->columnSpan('full');
    }
}
