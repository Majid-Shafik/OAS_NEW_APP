<?php

namespace App\Filament\Resources\Applicants\Pages;

use App\Filament\Resources\Applicants\ApplicantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Grid;

class EditApplicant extends EditRecord
{
    protected static string $resource = ApplicantResource::class;

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

                $baseUrl = env('YEMEN_EXAM_API_URL', 'http://yemenexam.gov.ye/Result/Data/');
                $username = env('YEMEN_EXAM_API_USERNAME', 'HighEdu');
                $password = env('YEMEN_EXAM_API_PASSWORD', 'HighEdu@2020');

                $found = false;
                $updates = [];
                foreach ([1, 2, 3] as $sec) {
                    $url = "{$baseUrl}{$sec}?username={$username}&password={$password}&number={$seat}&year={$year}&total={$mark}";
                    try {
                        $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                        if ($response->successful()) {
                            $data = $response->json();
                            if (!empty($data) && count($data) > 0) {
                                $found = true;
                                $student = $data[0];

                                $nameParts = explode(' ', trim($student['name'] ?? ''));

                                $updates = [
                                    'FULL_NAME' => $student['name'] ?? '',
                                    'FIRST_NAME' => $nameParts[0] ?? '',
                                    'LAST_NAME' => end($nameParts) ?? '',
                                    'SEC_SCHOOL_NAME' => $student['idSchool'] ?? '',
                                    'SEC_SCHOOL_RATE' => $student['Rate'] ?? '',
                                    'GENDER' => $student['gender'] ?? '',
                                    'PLACE_OF_BIRTH' => $student['city_birth'] ?? '',
                                    'COUNTRY_NAME' => $student['nationality'] ?? '',
                                    'YEMEN_NATIONAL' => ($student['nationality'] ?? '') == 'يمني' ? 1 : 0,
                                ];

                                if ($sec == 1) {
                                    $updates['SEC_SCHOOL_TYPE'] = 'علمي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 2) {
                                    $updates['SEC_SCHOOL_TYPE'] = 'ادبي';
                                    $updates['SEC_SCHOOL_OVERALLMARK'] = 800;
                                } elseif ($sec == 3) {
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

                                break;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }

                if (!$found) {
                    \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على بيانات هذا المتقدم في الوزارة!')->send();
                    throw new \Filament\Support\Exceptions\Halt();
                }

                return $updates;
            })
            ->form([
                Grid::make(2)->schema([
                    \Filament\Forms\Components\TextInput::make('FULL_NAME')->label('الاسم كامل')->readOnly()->columnSpanFull(),
                    \Filament\Forms\Components\TextInput::make('SEC_SCHOOL_TYPE')->label('نوع الثانوية')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('SEC_SCHOOL_RATE')->label('المعدل')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('SEC_SCHOOL_PROVINCE')->label('محافظة الثانوية')->readOnly(),
                    \Filament\Forms\Components\TextInput::make('SEC_SCHOOL_TERRITORY')->label('مديرية الثانوية')->readOnly(),
                ]),
                \Filament\Forms\Components\Hidden::make('FIRST_NAME'),
                \Filament\Forms\Components\Hidden::make('LAST_NAME'),
                \Filament\Forms\Components\Hidden::make('SEC_SCHOOL_NAME'),
                \Filament\Forms\Components\Hidden::make('GENDER'),
                \Filament\Forms\Components\Hidden::make('PLACE_OF_BIRTH'),
                \Filament\Forms\Components\Hidden::make('COUNTRY_NAME'),
                \Filament\Forms\Components\Hidden::make('YEMEN_NATIONAL'),
                \Filament\Forms\Components\Hidden::make('SEC_SCHOOL_OVERALLMARK'),
                \Filament\Forms\Components\Hidden::make('DATE_OF_BIRTH'),
                \Filament\Forms\Components\Hidden::make('SEC_SCHOOL_PLACE'),
                \Filament\Forms\Components\Hidden::make('TERRITORY'),
                \Filament\Forms\Components\Hidden::make('PROVINCE'),
            ])
            ->action(function (\App\Models\Applicant $record, array $data, \Filament\Resources\Pages\EditRecord $livewire) {
                $record->update($data);
                \Filament\Notifications\Notification::make()->success()->title('تم تحديث بيانات المتقدم بنجاح!')->send();

                // Refresh the form data
                if (method_exists($livewire, 'fillForm')) {
                    $livewire->fillForm();
                }
            });
    }
}
