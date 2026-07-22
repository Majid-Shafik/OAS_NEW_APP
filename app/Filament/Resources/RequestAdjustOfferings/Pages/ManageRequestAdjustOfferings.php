<?php

namespace App\Filament\Resources\RequestAdjustOfferings\Pages;

use App\Filament\Resources\RequestAdjustOfferings\RequestAdjustOfferingResource;
use App\Models\RequestAdjustOffering;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ManageRequestAdjustOfferings extends ManageRecords
{
    protected static string $resource = RequestAdjustOfferingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['RECORDED_ON'] = now();
                    $data['ADD_BY'] = auth()->id() ?? 1;

                    return $data;
                })
                ->after(function (array $data, RequestAdjustOffering $record, Component $livewire) {
                    $state = $livewire->form->getState();
                    $file = $state['un_attachment'] ?? null;
                    if ($file) {
                        $filePath = is_array($file) ? array_values($file)[0] : $file;
                        if (Storage::disk('public')->exists($filePath)) {
                            $dir = dirname($record->getUnAttachmentPath());
                            File::ensureDirectoryExists($dir);
                            file_put_contents($record->getUnAttachmentPath(), Storage::disk('public')->get($filePath));
                            Storage::disk('public')->delete($filePath);
                        }
                    }
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->badge(static::getModel()::count()),
            'pending' => Tab::make('قيد المراجعة')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('ACCEPT'))
                ->badge(static::getModel()::whereNull('ACCEPT')->count())
                ->badgeColor('warning'),
            'accepted' => Tab::make('مقبولة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ACCEPT', 1))
                ->badge(static::getModel()::where('ACCEPT', 1)->count())
                ->badgeColor('success'),
            'rejected' => Tab::make('مرفوضة')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('ACCEPT', 0))
                ->badge(static::getModel()::where('ACCEPT', 0)->count())
                ->badgeColor('danger'),
        ];
    }
}
