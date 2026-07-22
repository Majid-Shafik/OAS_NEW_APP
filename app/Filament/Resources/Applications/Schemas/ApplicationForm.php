<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\StudyType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('UNID')
                    ->label(__('University'))
                    ->relationship('university', 'U_NAME')
                    ->required(),
                TextInput::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT'))
                    ->required()
                    ->numeric(),
                Select::make('APPLICANT_IDENT')
                    ->label(__('APPLICANT_IDENT'))
                    ->options(Applicant::pluck('FULL_NAME', 'APPLICANT_IDENT'))
                    ->searchable()
                    ->required(),
                Select::make('FACULTY_IDENT')
                    ->label(__('Faculty'))
                    ->options(Faculty::pluck('FACULTY_NAME', 'FACULTY_IDENT')),
                Select::make('PROGRAM_IDENT')
                    ->label(__('Program'))
                    ->options(Program::pluck('PROGRAM_NAME', 'PROGRAM_IDENT')),
                Select::make('STUDYTYPE_IDENT')
                    ->label(__('STUDYTYPE_IDENT'))
                    ->options(StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT')),
                Select::make('CHOICE_NO')
                    ->label(__('CHOICE_NO'))
                    ->options([
                        '1' => 'الرغبة الأولى',
                        '2' => 'الرغبة الثانية',
                        '3' => 'الرغبة الثالثة',
                        '4' => 'الرغبة الرابعة',
                    ]),
                Select::make('PAYMENT_FLAG')
                    ->label(__('PAYMENT_FLAG'))
                    ->relationship('paymentMethod', 'PAY_METHOD'),
                Select::make('STATUS')
                    ->label(__('STATUS'))
                    ->badge()
                    ->options(ApplicationStatus::class)
                    ->required(),
            ]);
    }
}
