<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSyncService
{
    /**
     * الحصول على قائمة قواعد البيانات المستهدفة المتاحة للنشر إليها (باستثناء القاعدة الحالية)
     *
     * @return array<string, string>
     */
    public static function getAvailableTargetDatabases(): array
    {
        $allDatabases = config('academic_years.databases', []);
        $currentDb = session('tenant_database', config('academic_years.default_database'));

        $targets = [];
        foreach ($allDatabases as $dbName => $label) {
            if ($dbName !== $currentDb) {
                $targets[$dbName] = "{$label} ({$dbName})";
            }
        }

        return $targets;
    }

    /**
     * نشر ومزامنة الأدوار والصلاحيات وتعيينات المستخدمين من القاعدة الحالية إلى قواعد البيانات المحددة.
     *
     * آلية العمل:
     * 1. الأدوار الوظيفية (roles): إذا كان الدور موجوداً يتم تعديل بياناته، وإذا لم يكن موجوداً يتم إضافته.
     * 2. الصلاحيات (permissions): تصفير الترقيم وحذف الصلاحيات السابقة وإعادة بنائها مطابقة للمصدر.
     * 3. ربط الأدوار بالصلاحيات (role_has_permissions): حذف الربط السابق وإعادة إسناده بالكامل.
     * 4. تعيين الأدوار للمستخدمين (model_has_roles): حذف التعيينات السابقة وإعادة إسنادها بالكامل.
     * 5. الصلاحيات المباشرة للمستخدمين (model_has_permissions): حذفها وإعادة إسنادها.
     * 6. الأدوار المسموح بإدارتها (role_manageable_roles): تصفير الترقيم وحذفها وإعادة إسنادها.
     *
     * @param array<string> $targetDatabases
     * @param array<int>|null $roleIds (إذا كانت null فسيتم نشر كافة الأدوار والصلاحيات)
     * @param bool $syncUserRoles
     * @param bool $syncManageableRoles
     * @param bool $overwrite
     * @return array{success: bool, synced_databases: array<string>, errors: array<string, string>, message: string}
     */
    public function sync(
        array $targetDatabases,
        ?array $roleIds = null,
        bool $syncUserRoles = true,
        bool $syncManageableRoles = true,
        bool $overwrite = true
    ): array {
        // التحقق من الصلاحيات: مقتصرة فقط على المشرف العام (Admin / Super Admin)
        $user = auth()->user();
        if ($user && method_exists($user, 'isAdmin') && !$user->isAdmin()) {
            return [
                'success' => false,
                'synced_databases' => [],
                'errors' => ['unauthorized' => 'غير مصرح لك بنشر الصلاحيات.'],
                'message' => 'عذراً، وظيفة نشر ومزامنة الصلاحيات مقتصرة فقط على المشرف العام (Admin).',
            ];
        }

        $originalDb = session('tenant_database', config('academic_years.default_database'));
        $isSyncingAll = empty($roleIds);

        // 1. قراءة كافة البيانات مباشرة من جداول القاعدة المصدر (الحالية)
        try {
            Config::set('database.connections.tenant.database', $originalDb);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            // قراءة الأدوار
            $rolesQuery = DB::connection('tenant')->table('roles');
            if (!$isSyncingAll) {
                $rolesQuery->whereIn('id', $roleIds);
            }
            $sourceRoles = $rolesQuery->get();

            if ($sourceRoles->isEmpty()) {
                return [
                    'success' => false,
                    'synced_databases' => [],
                    'errors' => [],
                    'message' => 'لا توجد أدوار محددة للنشر.',
                ];
            }

            $sourceRoleIds = $sourceRoles->pluck('id')->toArray();

            // خرائط مطابقة المعرفات في القاعدة المصدر (ID -> Unique Key)
            $sourceRoleIdToKey = [];
            foreach ($sourceRoles as $role) {
                $sourceRoleIdToKey[$role->id] = "{$role->name}|{$role->guard_name}";
            }

            // قراءة الصلاحيات
            $sourcePermissions = DB::connection('tenant')->table('permissions')->get();
            $sourcePermIdToKey = [];
            foreach ($sourcePermissions as $perm) {
                $sourcePermIdToKey[$perm->id] = "{$perm->name}|{$perm->guard_name}";
            }

            // قراءة جدول ربط الصلاحيات بالأدوار (role_has_permissions)
            $sourceRoleHasPermissions = DB::connection('tenant')->table('role_has_permissions')
                ->whereIn('role_id', $sourceRoleIds)
                ->get();

            // قراءة جدول تعيين الأدوار للمستخدمين (model_has_roles)
            $sourceModelHasRoles = Schema::connection('tenant')->hasTable('model_has_roles')
                ? DB::connection('tenant')->table('model_has_roles')->whereIn('role_id', $sourceRoleIds)->get()
                : collect();

            // قراءة جدول الصلاحيات المباشرة للمستخدمين (model_has_permissions)
            $sourceModelHasPermissions = Schema::connection('tenant')->hasTable('model_has_permissions')
                ? DB::connection('tenant')->table('model_has_permissions')->get()
                : collect();

            // قراءة جدول الأدوار المسموح بإدارتها (role_manageable_roles)
            $sourceRoleManageable = Schema::connection('tenant')->hasTable('role_manageable_roles')
                ? DB::connection('tenant')->table('role_manageable_roles')->whereIn('role_id', $sourceRoleIds)->get()
                : collect();

        } catch (Exception $e) {
            Log::error('RolePermissionSyncService source read error: ' . $e->getMessage());
            return [
                'success' => false,
                'synced_databases' => [],
                'errors' => [$originalDb => $e->getMessage()],
                'message' => 'حدث خطأ أثناء قراءة البيانات من القاعدة الحالية (' . $originalDb . '): ' . $e->getMessage(),
            ];
        }

        $syncedDatabases = [];
        $errors = [];

        // 2. المزامنة مع كل قاعدة مستهدفة
        try {
            foreach ($targetDatabases as $targetDb) {
                try {
                    Config::set('database.connections.tenant.database', $targetDb);
                    DB::purge('tenant');
                    DB::setDefaultConnection('tenant');

                    // أ. التأكد من وجود كافة الجداول والأعمدة في القاعدة المستهدفة (DDL خارج الـ Transaction)
                    $this->ensureTablesExistOnTarget($targetDb);

                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');

                    // ب. تفريغ الجداول وتصفير العداد التلقائي (DDL) قبل بدء المعاملة لضمان عدم حدوث Implicit Commit
                    if ($isSyncingAll && $overwrite) {
                        DB::connection('tenant')->table('role_has_permissions')->delete();
                        if (Schema::connection('tenant')->hasTable('model_has_roles')) {
                            DB::connection('tenant')->table('model_has_roles')->delete();
                        }
                        if (Schema::connection('tenant')->hasTable('model_has_permissions')) {
                            DB::connection('tenant')->table('model_has_permissions')->delete();
                        }
                        if (Schema::connection('tenant')->hasTable('role_manageable_roles')) {
                            DB::connection('tenant')->table('role_manageable_roles')->delete();
                            DB::connection('tenant')->statement('ALTER TABLE `role_manageable_roles` AUTO_INCREMENT = 1;');
                        }
                        DB::connection('tenant')->table('permissions')->delete();
                        DB::connection('tenant')->statement('ALTER TABLE `permissions` AUTO_INCREMENT = 1;');
                    }

                    // بدء المعاملة لعمليات الإدخال
                    DB::connection('tenant')->beginTransaction();

                    // -------------------------------------------------------------
                    // الخطوة 1: مزامنة بيانات الأدوار (roles)
                    // إذا كان الدور موجوداً يتم تعديله، وإذا لم يكن موجوداً يتم إضافته
                    // -------------------------------------------------------------
                    $targetKeyToRoleId = []; // [ "name|guard_name" => target_role_id ]
                    foreach ($sourceRoles as $role) {
                        $existingRole = DB::connection('tenant')->table('roles')
                            ->where('name', $role->name)
                            ->where('guard_name', $role->guard_name)
                            ->first();

                        if ($existingRole) {
                            DB::connection('tenant')->table('roles')
                                ->where('id', $existingRole->id)
                                ->update([
                                    'label' => $role->label ?? null,
                                    'updated_at' => now(),
                                ]);
                            $targetRoleId = $existingRole->id;
                        } else {
                            $targetRoleId = DB::connection('tenant')->table('roles')->insertGetId([
                                'name' => $role->name,
                                'guard_name' => $role->guard_name,
                                'label' => $role->label ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        $targetKeyToRoleId["{$role->name}|{$role->guard_name}"] = $targetRoleId;
                    }

                    $targetRoleIdsToUpdate = array_values($targetKeyToRoleId);

                    // -------------------------------------------------------------
                    // الخطوة 2: الصلاحيات (permissions)
                    // -------------------------------------------------------------
                    $targetKeyToPermId = []; // [ "name|guard_name" => target_permission_id ]

                    if ($isSyncingAll && $overwrite) {
                        foreach ($sourcePermissions as $perm) {
                            $newPermId = DB::connection('tenant')->table('permissions')->insertGetId([
                                'id' => $perm->id,
                                'name' => $perm->name,
                                'guard_name' => $perm->guard_name,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $targetKeyToPermId["{$perm->name}|{$perm->guard_name}"] = $newPermId;
                        }
                    } else {
                        // عند مزامنة دور منفرد
                        foreach ($sourcePermissions as $perm) {
                            $existingPerm = DB::connection('tenant')->table('permissions')
                                ->where('name', $perm->name)
                                ->where('guard_name', $perm->guard_name)
                                ->first();

                            if ($existingPerm) {
                                $targetPermId = $existingPerm->id;
                            } else {
                                $targetPermId = DB::connection('tenant')->table('permissions')->insertGetId([
                                    'name' => $perm->name,
                                    'guard_name' => $perm->guard_name,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            }

                            $targetKeyToPermId["{$perm->name}|{$perm->guard_name}"] = $targetPermId;
                        }
                    }

                    // -------------------------------------------------------------
                    // الخطوة 3: ربط الأدوار بالصلاحيات (role_has_permissions)
                    // -------------------------------------------------------------
                    if (!$isSyncingAll && $overwrite) {
                        DB::connection('tenant')->table('role_has_permissions')
                            ->whereIn('role_id', $targetRoleIdsToUpdate)
                            ->delete();
                    }

                    $rolePermInserts = [];
                    foreach ($sourceRoleHasPermissions as $rhp) {
                        $roleKey = $sourceRoleIdToKey[$rhp->role_id] ?? null;
                        $permKey = $sourcePermIdToKey[$rhp->permission_id] ?? null;

                        if ($roleKey && $permKey && isset($targetKeyToRoleId[$roleKey], $targetKeyToPermId[$permKey])) {
                            $tRoleId = $targetKeyToRoleId[$roleKey];
                            $tPermId = $targetKeyToPermId[$permKey];

                            $rolePermInserts[] = [
                                'role_id' => $tRoleId,
                                'permission_id' => $tPermId,
                            ];
                        }
                    }

                    if (!empty($rolePermInserts)) {
                        foreach (array_chunk($rolePermInserts, 500) as $chunk) {
                            DB::connection('tenant')->table('role_has_permissions')->insert($chunk);
                        }
                    }

                    // -------------------------------------------------------------
                    // الخطوة 4: تعيين الأدوار للمستخدمين (model_has_roles)
                    // -------------------------------------------------------------
                    if ($syncUserRoles && Schema::connection('tenant')->hasTable('model_has_roles')) {
                        if (!$isSyncingAll && $overwrite) {
                            DB::connection('tenant')->table('model_has_roles')
                                ->whereIn('role_id', $targetRoleIdsToUpdate)
                                ->delete();
                        }

                        $modelRoleInserts = [];
                        foreach ($sourceModelHasRoles as $mhr) {
                            $roleKey = $sourceRoleIdToKey[$mhr->role_id] ?? null;

                            if ($roleKey && isset($targetKeyToRoleId[$roleKey])) {
                                $tRoleId = $targetKeyToRoleId[$roleKey];

                                $modelRoleInserts[] = [
                                    'role_id' => $tRoleId,
                                    'model_type' => $mhr->model_type,
                                    'model_id' => $mhr->model_id,
                                ];
                            }
                        }

                        if (!empty($modelRoleInserts)) {
                            foreach (array_chunk($modelRoleInserts, 500) as $chunk) {
                                DB::connection('tenant')->table('model_has_roles')->insert($chunk);
                            }
                        }
                    }

                    // -------------------------------------------------------------
                    // الخطوة 5: الصلاحيات المباشرة للمستخدمين (model_has_permissions)
                    // -------------------------------------------------------------
                    if ($syncUserRoles && Schema::connection('tenant')->hasTable('model_has_permissions')) {
                        $modelPermInserts = [];
                        foreach ($sourceModelHasPermissions as $mhp) {
                            $permKey = $sourcePermIdToKey[$mhp->permission_id] ?? null;

                            if ($permKey && isset($targetKeyToPermId[$permKey])) {
                                $tPermId = $targetKeyToPermId[$permKey];

                                $modelPermInserts[] = [
                                    'permission_id' => $tPermId,
                                    'model_type' => $mhp->model_type,
                                    'model_id' => $mhp->model_id,
                                ];
                            }
                        }

                        if (!empty($modelPermInserts)) {
                            foreach (array_chunk($modelPermInserts, 500) as $chunk) {
                                DB::connection('tenant')->table('model_has_permissions')->insert($chunk);
                            }
                        }
                    }

                    // -------------------------------------------------------------
                    // الخطوة 6: الأدوار المسموح بإدارتها (role_manageable_roles)
                    // -------------------------------------------------------------
                    if ($syncManageableRoles && Schema::connection('tenant')->hasTable('role_manageable_roles')) {
                        if (!$isSyncingAll && $overwrite) {
                            DB::connection('tenant')->table('role_manageable_roles')
                                ->whereIn('role_id', $targetRoleIdsToUpdate)
                                ->delete();
                        }

                        $manageableInserts = [];
                        foreach ($sourceRoleManageable as $mng) {
                            $roleKey = $sourceRoleIdToKey[$mng->role_id] ?? null;
                            $manageableKey = $sourceRoleIdToKey[$mng->manageable_role_id] ?? null;

                            if ($roleKey && $manageableKey && isset($targetKeyToRoleId[$roleKey], $targetKeyToRoleId[$manageableKey])) {
                                $tRoleId = $targetKeyToRoleId[$roleKey];
                                $tManageableId = $targetKeyToRoleId[$manageableKey];

                                $manageableInserts[] = [
                                    'role_id' => $tRoleId,
                                    'manageable_role_id' => $tManageableId,
                                    'created_by' => auth()->id() ?? 1,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }

                        if (!empty($manageableInserts)) {
                            foreach (array_chunk($manageableInserts, 500) as $chunk) {
                                DB::connection('tenant')->table('role_manageable_roles')->insert($chunk);
                            }
                        }
                    }

                    if (DB::connection('tenant')->transactionLevel() > 0) {
                        DB::connection('tenant')->commit();
                    }

                    DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');

                    // مسح كاش الصلاحيات للقاعدة المستهدفة
                    try {
                        app(PermissionRegistrar::class)->forgetCachedPermissions();
                    } catch (Exception) {
                        // ignore
                    }

                    $syncedDatabases[] = $targetDb;
                } catch (Exception $dbEx) {
                    try {
                        if (DB::connection('tenant')->transactionLevel() > 0) {
                            DB::connection('tenant')->rollBack();
                        }
                    } catch (Exception) {
                        // ignore rollback error
                    }
                    try {
                        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');
                    } catch (Exception) {
                        // ignore
                    }
                    Log::error("Failed to sync permissions to {$targetDb}: " . $dbEx->getMessage(), [
                        'trace' => $dbEx->getTraceAsString(),
                    ]);
                    $errors[$targetDb] = $dbEx->getMessage();
                }
            }
        } finally {
            // إعادة الاتصال بالقاعدة الأصلية دائماً
            Config::set('database.connections.tenant.database', $originalDb);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');
        }

        $allSuccess = count($errors) === 0 && count($syncedDatabases) > 0;
        $message = $allSuccess
            ? 'تم نشر ومزامنة الأدوار والصلاحيات وتعيينات المستخدمين بنجاح إلى (' . count($syncedDatabases) . ') قاعدة بيانات.'
            : (count($syncedDatabases) > 0
                ? 'تم النشر إلى بعض القواعد بنجاح (' . implode(', ', $syncedDatabases) . ')، وحدثت أخطاء في أخرى.'
                : 'فشل نشر الصلاحيات.');

        return [
            'success' => $allSuccess,
            'synced_databases' => $syncedDatabases,
            'errors' => $errors,
            'message' => $message,
        ];
    }

    /**
     * التحقق من وجود كافة جداول الصلاحيات في القاعدة المستهدفة وإنشائها تلقائياً إذا لم تكن موجودة
     */
    protected function ensureTablesExistOnTarget(string $targetDb): void
    {
        $schema = Schema::connection('tenant');

        // 1. جدول roles
        if (!$schema->hasTable('roles')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `roles` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `label` varchar(255) DEFAULT NULL,
                    `guard_name` varchar(255) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            if (!$schema->hasColumn('roles', 'label')) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `roles` ADD COLUMN `label` VARCHAR(255) NULL AFTER `name`;
                ");
            }
        }

        // 2. جدول permissions
        if (!$schema->hasTable('permissions')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `permissions` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) NOT NULL,
                    `guard_name` varchar(255) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 3. جدول role_has_permissions
        if (!$schema->hasTable('role_has_permissions')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `role_has_permissions` (
                    `permission_id` bigint(20) unsigned NOT NULL,
                    `role_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`permission_id`,`role_id`),
                    KEY `role_has_permissions_role_id_foreign` (`role_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 4. جدول model_has_roles
        if (!$schema->hasTable('model_has_roles')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `model_has_roles` (
                    `role_id` bigint(20) unsigned NOT NULL,
                    `model_type` varchar(255) NOT NULL,
                    `model_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`role_id`,`model_id`,`model_type`),
                    KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 5. جدول model_has_permissions
        if (!$schema->hasTable('model_has_permissions')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `model_has_permissions` (
                    `permission_id` bigint(20) unsigned NOT NULL,
                    `model_type` varchar(255) NOT NULL,
                    `model_id` bigint(20) unsigned NOT NULL,
                    PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
                    KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // 6. جدول role_manageable_roles
        if (!$schema->hasTable('role_manageable_roles')) {
            DB::connection('tenant')->statement("
                CREATE TABLE IF NOT EXISTS `role_manageable_roles` (
                    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `role_id` bigint(20) unsigned NOT NULL,
                    `manageable_role_id` bigint(20) unsigned NOT NULL,
                    `created_by` bigint(20) unsigned DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `role_manageable_roles_role_id_index` (`role_id`),
                    KEY `role_manageable_roles_manageable_role_id_index` (`manageable_role_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } else {
            if (!$schema->hasColumn('role_manageable_roles', 'created_at')) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `role_manageable_roles` ADD COLUMN `created_at` timestamp NULL DEFAULT NULL;
                ");
            }
            if (!$schema->hasColumn('role_manageable_roles', 'updated_at')) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `role_manageable_roles` ADD COLUMN `updated_at` timestamp NULL DEFAULT NULL;
                ");
            }
            if (!$schema->hasColumn('role_manageable_roles', 'created_by')) {
                DB::connection('tenant')->statement("
                    ALTER TABLE `role_manageable_roles` ADD COLUMN `created_by` bigint(20) unsigned DEFAULT NULL;
                ");
            }
        }
    }
}
