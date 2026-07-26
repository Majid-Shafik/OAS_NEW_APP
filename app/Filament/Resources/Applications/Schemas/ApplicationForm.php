<?php

namespace App\Filament\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use App\Models\Applicant;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\StudyType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use App\Models\University;
use App\Filament\Schemas\CoordinationSchema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('UNID')
                    ->label(__('University'))
                    ->options(function (Get $get) {
                        $applicantId = $get('APPLICANT_IDENT');
                        if (!$applicantId) return University::pluck('U_NAME', 'UNID');
                        
                        $applicant = Applicant::find($applicantId);
                        if (!$applicant) return [];

                        $offerings = CoordinationSchema::getFilteredOfferings(
                            $applicant->SEC_SCHOOL_TYPE,
                            $applicant->SEC_SCHOOL_RATE,
                            $applicant->SEC_SCHOOL_YEAR,
                            $applicant->YEMEN_NATIONAL
                        );
                        $unids = $offerings->pluck('UNID')->unique()->toArray();
                        return University::whereIn('UNID', $unids)->pluck('U_NAME', 'UNID');
                    })
                    ->live()
                    ->required(),
                TextInput::make('APPLICATION_IDENT')
                    ->label(__('APPLICATION_IDENT'))
                    ->required()
                    ->numeric(),
                Select::make('APPLICANT_IDENT')
                    ->label(__('APPLICANT_IDENT'))
                    ->options(Applicant::pluck('FULL_NAME', 'APPLICANT_IDENT'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('UNID', null);
                        $set('FACULTY_IDENT', null);
                        $set('PROGRAM_IDENT', null);
                        $set('STUDYTYPE_IDENT', null);
                    })
                    ->required(),
                Select::make('FACULTY_IDENT')
                    ->label(__('Faculty'))
                    ->options(function (Get $get) {
                        $unid = $get('UNID');
                        if (!$unid) return [];
                        
                        $applicantId = $get('APPLICANT_IDENT');
                        if (!$applicantId) return Faculty::where('UNID', $unid)->pluck('FACULTY_NAME', 'FACULTY_IDENT');

                        $applicant = Applicant::find($applicantId);
                        $offerings = CoordinationSchema::getFilteredOfferings(
                            $applicant->SEC_SCHOOL_TYPE,
                            $applicant->SEC_SCHOOL_RATE,
                            $applicant->SEC_SCHOOL_YEAR,
                            $applicant->YEMEN_NATIONAL
                        );
                        $faculties = $offerings->where('UNID', $unid)->pluck('FACULTY_IDENT')->unique()->toArray();
                        return Faculty::where('UNID', $unid)->whereIn('FACULTY_IDENT', $faculties)->pluck('FACULTY_NAME', 'FACULTY_IDENT');
                    })
                    ->live(),
                Select::make('PROGRAM_IDENT')
                    ->label(__('Program'))
                    ->options(function (Get $get) {
                        $unid = $get('UNID');
                        $facultyId = $get('FACULTY_IDENT');
                        if (!$unid || !$facultyId) return [];

                        $applicantId = $get('APPLICANT_IDENT');
                        if (!$applicantId) return Program::where('UNID', $unid)->where('FACULTY_IDENT', $facultyId)->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');

                        $applicant = Applicant::find($applicantId);
                        $offerings = CoordinationSchema::getFilteredOfferings(
                            $applicant->SEC_SCHOOL_TYPE,
                            $applicant->SEC_SCHOOL_RATE,
                            $applicant->SEC_SCHOOL_YEAR,
                            $applicant->YEMEN_NATIONAL
                        );
                        $programs = $offerings->where('UNID', $unid)->where('FACULTY_IDENT', $facultyId)->pluck('PROGRAM_IDENT')->unique()->toArray();
                        return Program::where('UNID', $unid)->where('FACULTY_IDENT', $facultyId)->whereIn('PROGRAM_IDENT', $programs)->pluck('PROGRAM_NAME', 'PROGRAM_IDENT');
                    })
                    ->live(),
                Select::make('STUDYTYPE_IDENT')
                    ->label(__('STUDYTYPE_IDENT'))
                    ->options(function (Get $get) {
                        $unid = $get('UNID');
                        $facultyId = $get('FACULTY_IDENT');
                        $programId = $get('PROGRAM_IDENT');
                        if (!$unid || !$facultyId || !$programId) return [];

                        $applicantId = $get('APPLICANT_IDENT');
                        if (!$applicantId) return StudyType::pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT');

                        $applicant = Applicant::find($applicantId);
                        $offerings = CoordinationSchema::getFilteredOfferings(
                            $applicant->SEC_SCHOOL_TYPE,
                            $applicant->SEC_SCHOOL_RATE,
                            $applicant->SEC_SCHOOL_YEAR,
                            $applicant->YEMEN_NATIONAL
                        );
                        $studyTypes = $offerings->where('UNID', $unid)->where('FACULTY_IDENT', $facultyId)->where('PROGRAM_IDENT', $programId)->pluck('STUDYTYPE_IDENT')->unique()->toArray();
                        return StudyType::whereIn('STUDYTYPE_IDENT', $studyTypes)->pluck('STUDYTYPE_NAME', 'STUDYTYPE_IDENT');
                    })
                    ->rule(
                        fn (Get $get, ?\App\Models\Application $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $query = \App\Models\Application::where('APPLICANT_IDENT', $get('APPLICANT_IDENT'))
                                ->where('PROGRAM_IDENT', $get('PROGRAM_IDENT'))
                                ->where('STUDYTYPE_IDENT', $value);

                            if ($record) {
                                $query->where('APPLICATION_IDENT', '!=', $record->APPLICATION_IDENT);
                            }

                            if ($query->exists()) {
                                $fail('هذا المتقدم مسجل مسبقاً في نفس التخصص لنفس النظام الدراسي.');
                            }
                        }
                    ),
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
                    ->options(ApplicationStatus::class)
                    ->required(),
            ]);
    }
}
