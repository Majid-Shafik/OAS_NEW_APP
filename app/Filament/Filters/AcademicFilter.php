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
        string $programColumn = 'PROGRAM_IDENT',
        ?string $relation = null
    ): Filter {
        return Filter::make($name)
            ->form([
                Select::make('UNID')
                    ->label('الجامعة')
                    ->options(University::pluck('U_NAME', 'UNID')->prepend('غير محدد', 0))
                    ->default(fn () => (int) (session('selected_unid', 0) ?: (auth()->user()?->UNID ?: 0)))
                    ->live()
                    ->searchable(),
                Select::make($facultyColumn)
                    ->label('الكلية')
                    ->options(function (Get $get) {
                        $unid = $get('UNID');
                        if ($unid === null || $unid === '') {
                            $unid = session('selected_unid', 0) ?: (auth()->user()?->UNID ?: 0);
                        }
                        if (! $unid || $unid == '0') {
                            return Faculty::pluck('FACULTY_NAME', 'FACULTY_IDENT')->prepend('غير محدد', 0);
                        }

                        return Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT')->prepend('غير محدد', 0);
                    })
                    ->live()
                    ->searchable(),
                Select::make($programColumn)
                    ->label('التخصص')
                    ->options(function (Get $get) use ($facultyColumn) {
                        $unid = $get('UNID');
                        if ($unid === null || $unid === '') {
                            $unid = session('selected_unid', 0) ?: (auth()->user()?->UNID ?: 0);
                        }
                        $facultyId = $get($facultyColumn);
                        $query = Program::query();
                        if ($unid && $unid != '0') {
                            $query->where('UNID', $unid);
                        }
                        if ($facultyId && $facultyId != '0') {
                            $query->where('FACULTY_IDENT', $facultyId);
                        }

                        return $query->pluck('PROGRAM_NAME', 'PROGRAM_IDENT')->prepend('غير محدد', 0);
                    })
                    ->searchable(),
            ])
            ->query(function (Builder $query, array $data) use ($facultyColumn, $programColumn, $relation): Builder {
                $applyFilters = function (Builder $query) use ($data, $facultyColumn, $programColumn) {
                    $model = $query->getModel();
                    $dbColumns = \Illuminate\Support\Facades\Schema::connection($model->getConnectionName())->getColumnListing($model->getTable());
                    $hasFacultyCol = in_array($facultyColumn, $dbColumns);

                    return $query
                        ->when(
                            isset($data['UNID']) && $data['UNID'] !== '' && $data['UNID'] != '0',
                            fn (Builder $query): Builder => $query->where($model->getTable().'.UNID', $data['UNID'])
                        )
                        ->when(
                            isset($data[$facultyColumn]) && $data[$facultyColumn] !== '' && $data[$facultyColumn] != '0',
                            function (Builder $query) use ($data, $facultyColumn, $hasFacultyCol, $model): Builder {
                                if ($hasFacultyCol) {
                                    return $query->where($model->getTable().'.'.$facultyColumn, $data[$facultyColumn]);
                                } elseif (method_exists($model, 'program')) {
                                    return $query->whereHas('program', fn (Builder $q) => $q->where($facultyColumn, $data[$facultyColumn]));
                                }

                                return $query;
                            }
                        )
                        ->when(
                            isset($data[$programColumn]) && $data[$programColumn] !== '' && $data[$programColumn] != '0',
                            fn (Builder $query): Builder => $query->where($model->getTable().'.'.$programColumn, $data[$programColumn])
                        );
                };

                if ($relation) {
                    return $query->whereHas($relation, $applyFilters);
                }

                return $applyFilters($query);
            })
            ->columns(3)
            ->columnSpan('full');
    }
}
