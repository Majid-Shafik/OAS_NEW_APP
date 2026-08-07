<?php

namespace App\Filament\Resources\ProgramCapacities\Schemas;

use App\Models\Program;
use App\Models\StudyType;
use App\Models\University;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProgramCapacityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('UNID')
                    ->label('الجامعة')
                    ->options(University::pluck('U_NAME', 'UNID'))
                    ->required()
                    ->live()
                    ->searchable(),
                    
                Select::make('PROGRAM_IDENT')
                    ->label('التخصص')
                    ->options(function (Get $get) {
                        $unid = $get('UNID');
                        if (!$unid) return [];
                        return Program::where('UNID', $unid)->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                    })
                    ->required()
                    ->searchable(),
                    
                Select::make('STUDYTYPE_IDENT')
                    ->label('النظام الدراسي')
                    ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT'))
                    ->required()
                    ->searchable(),
                    
                TextInput::make('ENROLLMENT_CAPACITY')
                    ->label('الطاقة الاستيعابية')
                    ->numeric()
                    ->required(),
            ]);
    }
}
