<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public Collection $permissions;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->permissions = collect($data)
            ->filter(fn(mixed $permission, string $key): bool => !in_array($key, ['name', 'label', 'guard_name', 'select_all', Utils::getTenantModelForeignKey()]))
            ->values()
            ->flatten()
            ->unique();

        if (Utils::isTenancyEnabled() && Arr::has($data, Utils::getTenantModelForeignKey()) && filled($data[Utils::getTenantModelForeignKey()])) {
            return Arr::only($data, ['name', 'label', 'guard_name', Utils::getTenantModelForeignKey()]);
        }

        return Arr::only($data, ['name', 'label', 'guard_name']);
    }

    // protected function afterSave(): void
    // {
    //     $permissionModels = collect();
    //     $this->permissions->each(function (string $permission) use ($permissionModels): void {
    //         $permissionModels->push(Utils::getPermissionModel()::firstOrCreate([
    //             'name' => $permission,
    //             'guard_name' => $this->data['guard_name'],
    //         ]));
    //     });

    //     // @phpstan-ignore-next-line
    //     $this->record->syncPermissions($permissionModels);
    // }
    protected function afterSave(): void
    {
        $permissionModels = collect();

        // حفظ الصلاحيات كما كان موجوداً
        $this->permissions->each(function (string $permission) use ($permissionModels): void {
            $permissionModels->push(
                Utils::getPermissionModel()::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $this->data['guard_name'],
                ])
            );
        });

        // @phpstan-ignore-next-line
        $this->record->syncPermissions($permissionModels);

        // ==========================
        // حفظ الأدوار المسموح بإدارتها
        // ==========================
        if (isset($this->data['manageable_roles'])) {
            $this->record->manageableRoles()->sync($this->data['manageable_roles']);
        }

        // ==========================
        // حفظ النطاق (الفروع والوحدات)
        // ==========================
        if (isset($this->data['scopes']['branch_id']) || isset($this->data['scopes']['unit_id'])) {
            // حذف السجلات القديمة أولاً
            $this->record->scopes()->delete();

            // حفظ الفروع والوحدات بشكل مرتبط (كل branch مع كل unit)
            $branchIds = $this->data['scopes']['branch_id'] ?? [];
            $unitIds = $this->data['scopes']['unit_id'] ?? [];

            if (!empty($branchIds) || !empty($unitIds)) {
                foreach ($branchIds as $branchId) {
                    // إذا لم يكن هناك وحدات محددة، نسجل فقط الفرع
                    if (empty($unitIds)) {
                        $this->record->scopes()->create([
                            'branch_id' => $branchId,
                            'unit_id' => null,
                        ]);
                    } else {
                        foreach ($unitIds as $unitId) {
                            $this->record->scopes()->create([
                                'branch_id' => $branchId,
                                'unit_id' => $unitId,
                            ]);
                        }
                    }
                }

                // إذا لم يتم تحديد فروع لكن وحدات موجودة
                if (empty($branchIds) && !empty($unitIds)) {
                    foreach ($unitIds as $unitId) {
                        $this->record->scopes()->create([
                            'branch_id' => null,
                            'unit_id' => $unitId,
                        ]);
                    }
                }
            }
        }
    }
}
