<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Services\RolePermissionSyncService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('syncPermissionsToDatabases')
                ->label('نشر ومزامنة الصلاحيات لقواعد البيانات')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->visible(fn () => auth()->user()?->isAdmin())
                ->modalHeading('نشر ومزامنة الصلاحيات إلى قواعد بيانات أخرى')
                ->modalDescription('اختر قواعد البيانات التي ترغب في نسخ وتحديث كافة الأدوار والصلاحيات الحالية إليها.')
                ->modalSubmitActionLabel('بدء النشر والمزامنة')
                ->modalIcon('heroicon-o-arrow-path-rounded-square')
                ->form([
                    CheckboxList::make('target_databases')
                        ->label('قواعد البيانات المستهدفة')
                        ->options(fn () => RolePermissionSyncService::getAvailableTargetDatabases())
                        ->default(fn () => array_keys(RolePermissionSyncService::getAvailableTargetDatabases()))
                        ->bulkToggleable()
                        ->searchable()
                        ->required()
                        ->minItems(1, 'يجب اختيار قاعدة بيانات واحدة على الأقل.'),

                    Toggle::make('sync_user_roles')
                        ->label('مزامنة تعيين الأدوار للمستخدمين (model_has_roles)')
                        ->helperText('نسخ أدوار المستخدمين المعينين في القاعدة الحالية إلى نفس المستخدمين في القواعد المستهدفة.')
                        ->default(true),

                    Toggle::make('sync_manageable_roles')
                        ->label('مزامنة الأدوار المسموح بإدارتها (Manageable Roles)')
                        ->default(true),

                    Toggle::make('overwrite')
                        ->label('إعادة مطابقة الصلاحيات بدقة (Overwrite & Match)')
                        ->helperText('عند التفعيل، سيتم مسح الصلاحيات السابقة غير المطابقة في القواعد المستهدفة ومطابقتها تماماً مع القاعدة الحالية.')
                        ->default(true),
                ])
                ->action(function (array $data, RolePermissionSyncService $syncService): void {
                    $result = $syncService->sync(
                        targetDatabases: $data['target_databases'] ?? [],
                        roleIds: null,
                        syncUserRoles: (bool) ($data['sync_user_roles'] ?? true),
                        syncManageableRoles: (bool) ($data['sync_manageable_roles'] ?? true),
                        overwrite: (bool) ($data['overwrite'] ?? true)
                    );

                    if ($result['success']) {
                        Notification::make()
                            ->title('تم النشر بنجاح')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        $errorDetails = [];
                        foreach ($result['errors'] as $db => $err) {
                            $errorDetails[] = "{$db}: {$err}";
                        }
                        $body = $result['message'];
                        if (!empty($errorDetails)) {
                            $body .= "\n\nالسبب: " . implode("\n", $errorDetails);
                        }

                        Notification::make()
                            ->title('تنبيه أثناء النشر')
                            ->body($body)
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
