<?php

namespace App\Filament\Resources\ClearingApplicants\Pages;

use App\Filament\Resources\ClearingApplicants\ClearingApplicantResource;
use App\Filament\Resources\ClearingApplicants\Widgets\ClearingApplicantStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListClearingApplicants extends ListRecords
{
    protected static string $resource = ClearingApplicantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClearingApplicantStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge($this->getModel()::count()),
            'pending_first' => Tab::make('بانتظار المراجعة الأولى')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn($q) => $q->whereNull('REVIEWED')->orWhere('REVIEWED', 0)))
                ->badge($this->getModel()::where(fn($q) => $q->whereNull('REVIEWED')->orWhere('REVIEWED', 0))->count()),
            'pending_second' => Tab::make('بانتظار المراجعة الثانية')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('REVIEWED', 1)->where(fn($q) => $q->whereNull('SECOND_REVIEWED')->orWhere('SECOND_REVIEWED', 0)))
                ->badge($this->getModel()::where('REVIEWED', 1)->where(fn($q) => $q->whereNull('SECOND_REVIEWED')->orWhere('SECOND_REVIEWED', 0))->count()),
            'rejected' => Tab::make('مرفوض')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('REVIEWED', 2)->orWhere('SECOND_REVIEWED', 2))
                ->badge($this->getModel()::where('REVIEWED', 2)->orWhere('SECOND_REVIEWED', 2)->count()),
            'approved' => Tab::make('معتمد نهائياً')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('REVIEWED', 1)->where('SECOND_REVIEWED', 1))
                ->badge($this->getModel()::where('REVIEWED', 1)->where('SECOND_REVIEWED', 1)->count()),
        ];
    }
}
