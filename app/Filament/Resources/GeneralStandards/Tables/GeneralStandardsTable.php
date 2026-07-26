<?php

namespace App\Filament\Resources\GeneralStandards\Tables;

use App\Filament\Filters\AcademicFilter;
use App\Models\GeneralStandard;
use App\Models\University;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema as DbSchema;

class GeneralStandardsTable
{
    public static function configure(Table $table): Table
    {
        $columnsArray = [
            TextColumn::make('university.U_NAME')->label('الجامعة')->sortable()->searchable(),
            TextColumn::make('faculty.FACULTY_NAME')->label('الكلية')->sortable()->searchable(),
            TextColumn::make('PROGRAM_NAME')->label('التخصص')->sortable()->searchable(),
        ];

        $model = new GeneralStandard();
        $dbColumns = DbSchema::connection($model->getConnectionName())->getColumnListing($model->getTable());

        for ($year = 2011; $year <= (int) date('Y') + 1; $year++) {
            $column = 'Y_' . $year;
            if (in_array($column, $dbColumns)) {
                $columnsArray[] = TextColumn::make($column)->label($year)->sortable()->prefix('%');
            }
        }

        return $table
            ->defaultSort('UNID', 'desc')
            ->columns($columnsArray)
            ->filters([
                AcademicFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
