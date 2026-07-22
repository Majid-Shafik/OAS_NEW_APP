<?php

namespace App\Filament\Resources\Offerings\Pages;

use App\Filament\Resources\Offerings\OfferingResource;
use App\Models\OfferingGroup;
use App\Models\Program;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManageOfferings extends ManageRecords
{
    protected static string $resource = OfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->using(function (array $data, string $model): Model {
                    return DB::transaction(function () use ($data, $model) {
                        $programName = Program::where('PROGRAM_IDENT', $data['PROGRAM_IDENT'] ?? 0)->value('PROGRAM_NAME') ?? 'جديدة';

                        $group = OfferingGroup::create([
                            'UNID' => $data['UNID'],
                            'FACULTY_IDENT' => $data['FACULTY_IDENT'],
                            'STUDYTYPE_IDENT' => $data['STUDYTYPE_IDENT'],
                            'DESCRIPTION' => 'مجموعة تنسيق - '.$programName,
                            'MIN_CHOICE' => 1,
                            'MAX_CHOICE' => 1,
                            'APPLYING_COST' => $data['STUDY_FEES'] ?? 0,
                            'COST_TYPE' => 'R',
                            'ENABLE_PAYMENT' => 1,
                            'EXAM_NO_REQUIRED' => isset($data['ENTRANCE_EXAM_REQUIRED']) && $data['ENTRANCE_EXAM_REQUIRED'] ? 1 : 0,
                            'RECORD_ON' => now(),
                            'RECORD_BY' => auth()->id() ?? 1,
                            'LAST_UPDATED_ON' => now(),
                            'LAST_UPDATED_BY' => auth()->id() ?? 1,
                        ]);

                        $data['OFFER_GROUP_IDENT'] = $group->OFFER_GROUP_IDENT;

                        // حفظ المعيار بعد مجموعة التنسيق داخل نفس الـ Transaction
                        return $model::create($data);
                    });
                }),
        ];
    }
}
