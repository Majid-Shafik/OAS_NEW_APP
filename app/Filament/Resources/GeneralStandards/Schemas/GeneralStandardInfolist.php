<?php

namespace App\Filament\Resources\GeneralStandards\Schemas;

use App\Models\GeneralStandard;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;

class GeneralStandardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $schemaArray = [
            TextEntry::make('university.U_NAME')->label('الجامعة'),
            TextEntry::make('faculty.FACULTY_NAME')->label('الكلية'),
            TextEntry::make('PROGRAM_IDENT')->label('رقم التخصص'),
            TextEntry::make('PROGRAM_NAME')->label('التخصص'),
        ];

        $model = new GeneralStandard();
        $columns = DbSchema::connection($model->getConnectionName())->getColumnListing($model->getTable());

        for ($year = 2011; $year <= (int) date('Y') + 1; $year++) {
            $column = 'Y_' . $year;
            if (in_array($column, $columns)) {
                $schemaArray[] = TextEntry::make($column)->label($year)->prefix('%');
            }
        }

        return $schema->schema($schemaArray)->columns(4);
    }
}
