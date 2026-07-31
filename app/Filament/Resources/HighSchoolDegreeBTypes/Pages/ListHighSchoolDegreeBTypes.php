<?php

namespace App\Filament\Resources\HighSchoolDegreeBTypes\Pages;

use App\Filament\Resources\HighSchoolDegreeBTypes\HighSchoolDegreeBTypeResource;
use App\Filament\Resources\HighSchoolDegreeBTypes\Widgets\HighSchoolDegreeBTypeStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListHighSchoolDegreeBTypes extends ListRecords
{
    protected static string $resource = HighSchoolDegreeBTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HighSchoolDegreeBTypeStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge($this->getModel()::count()),
            'approved' => Tab::make('المعتمد')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('APPROVED', 1))
                ->badge($this->getModel()::where('APPROVED', 1)->count()),
            'unapproved' => Tab::make('غير المعتمدين')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where(function ($q) {
                    $q->where('APPROVED', '!=', 1)->orWhereNull('APPROVED');
                }))
                ->badge($this->getModel()::where(function ($q) {
                    $q->where('APPROVED', '!=', 1)->orWhereNull('APPROVED');
                })->count()),
        ];
    }
}
