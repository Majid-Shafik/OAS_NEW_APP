<?php

namespace App\Filament\Resources\GeneralStandards\Schemas;

use App\Models\Faculty;
use App\Models\GeneralStandard;
use App\Models\University;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;

class GeneralStandardForm
{
    public static function configure(Schema $schema): Schema
    {
        $schemaArray = [
            Select::make('UNID')
                ->label('الجامعة')
                ->options(fn() => University::pluck('U_NAME', 'UNID'))
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('FACULTY_IDENT', null);
                    $set('PROGRAM_IDENT', null);
                    $set('PROGRAM_NAME', null);
                })
                ->required(),
            Select::make('FACULTY_IDENT')
                ->label('الكلية')
                ->options(function (Get $get) {
                    $unid = $get('UNID');
                    if (!$unid) return Faculty::pluck('FACULTY_NAME', 'FACULTY_IDENT');
                    return Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                })
                ->live()
                ->afterStateUpdated(function (Set $set) {
                    $set('PROGRAM_IDENT', null);
                    $set('PROGRAM_NAME', null);
                })
                ->required(),
            Select::make('PROGRAM_IDENT')
                ->label('التخصص')
                ->options(function (Get $get) {
                    $unid = $get('UNID');
                    $faculty = $get('FACULTY_IDENT');
                    if (!$unid || !$faculty) return \App\Models\Program::pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                    return \App\Models\Program::where('UNID', $unid)->where('FACULTY_IDENT', $faculty)->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                })
                ->live()
                ->afterStateUpdated(function (Set $set, $state, Get $get) {
                    $unid = $get('UNID');
                    $faculty = $get('FACULTY_IDENT');
                    if ($unid && $faculty && $state) {
                        $program = \App\Models\Program::where('UNID', $unid)
                            ->where('FACULTY_IDENT', $faculty)
                            ->where('PROGRAM_IDENT', $state)
                            ->first();
                        if ($program) {
                            $set('PROGRAM_NAME', $program->PROGRAM_NAME);
                        }
                    } else {
                        $set('PROGRAM_NAME', null);
                    }
                })
                ->required(),
            TextInput::make('PROGRAM_NAME')
                ->label('اسم التخصص')
                ->disabled()
                ->dehydrated()
                ->required(),
        ];

        $model = new GeneralStandard();
        $columns = DbSchema::connection($model->getConnectionName())->getColumnListing($model->getTable());

        for ($year = 2011; $year <= (int) date('Y') + 1; $year++) {
            $column = 'Y_' . $year;
            if (in_array($column, $columns)) {
                $schemaArray[] = TextInput::make($column)->label($year)->numeric()->suffix('%');
            }
        }

        return $schema->schema($schemaArray)->columns(4);
    }
}
