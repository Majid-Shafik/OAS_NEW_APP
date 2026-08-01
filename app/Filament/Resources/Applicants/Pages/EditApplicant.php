<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class EditApplicant extends EditRecord
{
    protected static string $resource = ApplicantResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['COUNTRY_IDENT']) && !empty($data['COUNTRY_NAME'])) {
            $data['COUNTRY_IDENT'] = \App\Models\Country::where('COUNTRY_NAME', $data['COUNTRY_NAME'])->value('COUNTRY_IDENT');
            if (empty($data['COUNTRY_IDENT']) && ($data['YEMEN_NATIONAL'] ?? 0) == 1) {
                $data['COUNTRY_IDENT'] = 242;
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getApiRefreshAction(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getApiRefreshAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('fetchApi')
            ->label('تحديث من الوزارة')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(fn() => auth()->user()->can('UpdateFromMinistryApplicant:Applicant'))
            ->modalHeading('بيانات المتقدم من الوزارة')
            ->modalSubmitActionLabel('تحديث السجل')
            ->modalCancelActionLabel('إلغاء')
            ->fillForm(function (\App\Models\Applicant $record) {
                $seat = $record->SEC_SCHOOL_SEATNO;
                $year = $record->SEC_SCHOOL_YEAR;
                $mark = intval($record->SEC_SCHOOL_MARK);

                $apiService = new \App\Services\MinistryApiService();
                $data = $apiService->fetchStudentData($year, $seat, $mark);
                $found = false;
                $updates = [];

                if (!empty($data) && (isset($data['seat_number']) || (is_array($data) && count($data) > 0))) {
                    $found = true;
                    $student = isset($data[0]) ? $data[0] : $data;

                                $nameParts = explode(' ', trim($student['name'] ?? ''));

                                $updates = [
                                    'FULL_NAME' => $student['name'] ?? '',
                                    'FIRST_NAME' => $nameParts[0] ?? '',
                                    'LAST_NAME' => end($nameParts) ?? '',
                                    'SEC_SCHOOL_NAME' => $student['idSchool'] ?? $student['school_name'] ?? '',
                                    'SEC_SCHOOL_RATE' => $student['Rate'] ?? $student['rate'] ?? '',
                                    'GENDER' => $student['gender'] ?? '',
                                    'PLACE_OF_BIRTH' => $student['city_birth'] ?? '',
                                    'COUNTRY_NAME' => $student['nationality'] ?? '',
                                    'YEMEN_NATIONAL' => ($student['nationality'] ?? '') == 'يمني' ? 1 : 0,
                                ];

                                $countryName = $student['nationality'] ?? '';
                                if ($updates['YEMEN_NATIONAL'] == 1 || in_array($countryName, ['يمني', 'يمنيه', 'يمنية', 'اليمن'])) {
                                    $updates['COUNTRY_IDENT'] = 242;
                                    $updates['COUNTRY_NAME'] = \App\Models\Country::where('COUNTRY_IDENT', 242)->value('COUNTRY_NAME') ?? 'اليمن';
                                } else {
                                    $updates['COUNTRY_IDENT'] = \App\Models\Country::where('COUNTRY_NAME', 'like', "%{$countryName}%")->value('COUNTRY_IDENT') ?? null;
                                }

                                $sec = $student['section_id'] ?? 1; // Default
                                if ($sec == 1 || ($student['type'] ?? '') === 'علمي') {
                                    $updates['SEC_SCHOOL_TYPE'] = 'علمي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 2 || ($student['type'] ?? '') === 'أدبي' || ($student['type'] ?? '') === 'ادبي') {
                                    $updates['SEC_SCHOOL_TYPE'] = 'ادبي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 3 || str_contains($student['type'] ?? '', 'شرعي')) {
                                    $updates['SEC_SCHOOL_TYPE'] = 'علوم شرعي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 1700;
                                }

                                if (isset($student['bod'])) {
                                    $updates['DATE_OF_BIRTH'] = date('Y-m-d', strtotime(str_replace('/', '-', $student['bod'])));
                                }

                                $cityAndProvince = explode('/', $student['city_study'] ?? '');
                                $lastPos = count($cityAndProvince) > 1 ? (count($cityAndProvince) - 1) : 0;
                                $updates['SEC_SCHOOL_TERRITORY'] = trim($cityAndProvince[0] ?? '');
                                $updates['SEC_SCHOOL_PROVINCE'] = trim($cityAndProvince[$lastPos] ?? '');
                                $updates['SEC_SCHOOL_PLACE'] = $student['city_study'] ?? '';
                                $Territory = explode('/', $student['city_birth'] ?? '');
                                $Province = count($Territory) > 1 ? (count($Territory) - 1) : 0;
                                $updates['TERRITORY'] = trim($Territory[0] ?? '');
                                $updates['PROVINCE'] = trim(trim($Territory[$Province] ?? ''));
                }

                if (!$found) {
                    \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على بيانات هذا المتقدم في الوزارة!')->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                return $updates;
            })
            ->form([
                 Grid::make(2)->schema([
                    
                     Section::make('البيانات الواردة من الوزارة')
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('FULL_NAME')
                                ->label('الاسم كامل')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->FULL_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_TYPE')
                                ->label('نوع الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_TYPE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_RATE')
                                ->label('المعدل')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_RATE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_NAME')
                                ->label('اسم المدرسة')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_PROVINCE')
                                ->label('محافظة الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_PROVINCE ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('SEC_SCHOOL_TERRITORY')
                                ->label('مديرية الثانوية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->SEC_SCHOOL_TERRITORY ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('GENDER')
                                ->label('الجنس')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->GENDER ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('DATE_OF_BIRTH')
                                ->label('تاريخ الميلاد')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->DATE_OF_BIRTH ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('PLACE_OF_BIRTH')
                                ->label('مكان الميلاد')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->PLACE_OF_BIRTH ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),
                            TextInput::make('COUNTRY_NAME')
                                ->label('الجنسية')->readOnly()
                                ->extraInputAttributes(fn ($state, $record) => $state != $record->COUNTRY_NAME ? ['style' => 'background-color: #fef08a !important; color: #000 !important;'] : []),

                            // Hidden necessary fields
                            Hidden::make('FIRST_NAME'),
                            Hidden::make('LAST_NAME'),
                            Hidden::make('YEMEN_NATIONAL'),
                            Hidden::make('SEC_SCHOOL_OVERALLMARK'),
                            Hidden::make('SEC_SCHOOL_PLACE'),
                            Hidden::make('TERRITORY'),
                            Hidden::make('PROVINCE'),
                        ]),

                    Section::make('البيانات الحالية في النظام')
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('old_FULL_NAME')->label('الاسم كامل')->state(fn ($record) => $record->FULL_NAME ?: '-'),
                            TextEntry::make('old_SEC_SCHOOL_TYPE')->label('نوع الثانوية')->state(fn ($record) => $record->SEC_SCHOOL_TYPE ?: '-'),
                            TextEntry::make('old_SEC_SCHOOL_RATE')->label('المعدل')->state(fn ($record) => $record->SEC_SCHOOL_RATE ?: '-'),
                            TextEntry::make('old_SEC_SCHOOL_NAME')->label('اسم المدرسة')->state(fn ($record) => $record->SEC_SCHOOL_NAME ?: '-'),
                            TextEntry::make('old_SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')->state(fn ($record) => $record->SEC_SCHOOL_PROVINCE ?: '-'),
                            TextEntry::make('old_SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')->state(fn ($record) => $record->SEC_SCHOOL_TERRITORY ?: '-'),
                            TextEntry::make('old_GENDER')->label('الجنس')->state(fn ($record) => $record->GENDER ?: '-'),
                            TextEntry::make('old_DATE_OF_BIRTH')->label('تاريخ الميلاد')->state(fn ($record) => $record->DATE_OF_BIRTH ?: '-'),
                            TextEntry::make('old_PLACE_OF_BIRTH')->label('مكان الميلاد')->state(fn ($record) => $record->PLACE_OF_BIRTH ?: '-'),
                            TextEntry::make('old_COUNTRY_NAME')->label('الجنسية')->state(fn ($record) => $record->COUNTRY_NAME ?: '-'),
                        ]),
                ])
            ])
            ->action(function (\App\Models\Applicant $record, array $data, EditRecord $livewire) {
                $record->update($data);
                \Filament\Notifications\Notification::make()->success()->title('تم تحديث بيانات المتقدم بنجاح!')->send();

                // Refresh the form data
                if (method_exists($livewire, 'fillForm')) {
                    $livewire->fillForm();
                }
            });
    }
}
