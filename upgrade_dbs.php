<?php

if (!isset($app) || !($app instanceof \Illuminate\Foundation\Application)) {
    require_once __DIR__.'/vendor/autoload.php';
    $app = require __DIR__.'/bootstrap/app.php';
    if ($app instanceof \Illuminate\Foundation\Application) {
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
}

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$databases = array_keys(config('academic_years.databases', []));

echo "=== بدء فحص وتحديث قواعد البيانات للأعوام الدراسية ===\n\n";

foreach ($databases as $dbName) {
    echo "--------------------------------------------------------\n";
    echo ">> جاري معالجة قاعدة البيانات: {$dbName}\n";

    try {
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        // 1. فحص وتعديل حقل USER_IDENT في جدول users
        if (Schema::hasTable('users')) {
            $userIdentCol = DB::select("SHOW COLUMNS FROM `users` LIKE 'USER_IDENT'");
            if (!empty($userIdentCol)) {
                $colInfo = $userIdentCol[0];
                if (strtolower($colInfo->Type) !== 'bigint unsigned' && strtolower($colInfo->Type) !== 'bigint(20) unsigned' || strpos(strtolower($colInfo->Extra), 'auto_increment') === false) {
                    try {
                        DB::statement("ALTER TABLE `users` MODIFY `USER_IDENT` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
                        echo "  [+] تم تعديل حقل USER_IDENT إلى BIGINT UNSIGNED AUTO_INCREMENT\n";
                    } catch (\Throwable $e) {
                        echo "  [!] تنبيه في USER_IDENT: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "  [*] حقل USER_IDENT محدث مسبقاً.\n";
                }
            }

            // 2. فحص وتوسيع حقل LOGON_PASS
            $logonPassCol = DB::select("SHOW COLUMNS FROM `users` LIKE 'LOGON_PASS'");
            if (!empty($logonPassCol)) {
                $type = strtolower($logonPassCol[0]->Type);
                if ($type !== 'varchar(255)' && $type !== 'varchar(200)') {
                    DB::statement("ALTER TABLE `users` CHANGE COLUMN `LOGON_PASS` `LOGON_PASS` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci' AFTER `LOGON_ID`");
                    echo "  [+] تم توسيع حقل LOGON_PASS إلى VARCHAR(255)\n";
                } else {
                    echo "  [*] حقل LOGON_PASS محدث مسبقاً ({$type}).\n";
                }
            }

            // 3. فحص وإضافة remember_token
            $rememberCol = DB::select("SHOW COLUMNS FROM `users` LIKE 'remember_token'");
            if (empty($rememberCol)) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `remember_token` VARCHAR(100) NULL DEFAULT NULL");
                echo "  [+] تم إضافة حقل remember_token\n";
            } else {
                echo "  [*] حقل remember_token موجود مسبقاً.\n";
            }
        }

        // 4. تحديث جدول province
        if (Schema::hasTable('province')) {
            $cols = Schema::getColumnListing('province');
            $idCol = null;
            foreach (['ID', 'id', 'PROVINCE_IDENT', 'province_ident', 'PROVINCE_ID', 'province_id'] as $candidate) {
                if (in_array($candidate, $cols)) {
                    $idCol = $candidate;
                    break;
                }
            }
            $nameCol = null;
            foreach (['NAME', 'name', 'PROVINCE_NAME', 'province_name'] as $candidate) {
                if (in_array($candidate, $cols)) {
                    $nameCol = $candidate;
                    break;
                }
            }

            if ($idCol && $nameCol) {
                $updated = DB::table('province')->where($idCol, 5)->where($nameCol, '!=', 'امانةالعاصمة')->update([$nameCol => 'امانةالعاصمة']);
                if ($updated) {
                    echo "  [+] تم تحديث اسم المحافظة {$idCol}=5 إلى 'امانةالعاصمة'\n";
                } else {
                    echo "  [*] جدول province محدث مسبقاً.\n";
                }
            }
        }

        // 5. جدول sessions
        DB::statement("
            CREATE TABLE IF NOT EXISTS `sessions` (
              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `user_id` int(10) DEFAULT NULL,
              `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `user_agent` text COLLATE utf8mb4_unicode_ci,
              `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `last_activity` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `sessions_user_id_index` (`user_id`),
              KEY `sessions_last_activity_index` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 6. جدول cache
        DB::statement("
            CREATE TABLE IF NOT EXISTS `cache` (
              `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `expiration` int(11) NOT NULL,
              PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 7. جدول cache_locks
        DB::statement("
            CREATE TABLE IF NOT EXISTS `cache_locks` (
              `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `expiration` int(11) NOT NULL,
              PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 8. جدول jobs
        DB::statement("
            CREATE TABLE IF NOT EXISTS `jobs` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `attempts` tinyint(3) unsigned NOT NULL,
              `reserved_at` int(10) unsigned DEFAULT NULL,
              `available_at` int(10) unsigned NOT NULL,
              `created_at` int(10) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `jobs_queue_index` (`queue`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 9. جدول job_batches
        DB::statement("
            CREATE TABLE IF NOT EXISTS `job_batches` (
              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `total_jobs` int(11) NOT NULL,
              `pending_jobs` int(11) NOT NULL,
              `failed_jobs` int(11) NOT NULL,
              `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `options` mediumtext COLLATE utf8mb4_unicode_ci,
              `cancelled_at` int(11) DEFAULT NULL,
              `created_at` int(11) NOT NULL,
              `finished_at` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 10. جدول failed_jobs
        DB::statement("
            CREATE TABLE IF NOT EXISTS `failed_jobs` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
              `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 11. جدول permissions
        DB::statement("
            CREATE TABLE IF NOT EXISTS `permissions` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 12. جدول roles
        DB::statement("
            CREATE TABLE IF NOT EXISTS `roles` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // فحص وإضافة حقل label في roles
        $labelCol = DB::select("SHOW COLUMNS FROM `roles` LIKE 'label'");
        if (empty($labelCol)) {
            DB::statement("ALTER TABLE `roles` ADD COLUMN `label` VARCHAR(255) NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `name`");
            echo "  [+] تم إضافة حقل label لجدول roles\n";
        }

        // 13. جدول model_has_permissions
        DB::statement("
            CREATE TABLE IF NOT EXISTS `model_has_permissions` (
              `permission_id` bigint(20) unsigned NOT NULL,
              `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `model_id` int(10) NOT NULL,
              PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
              KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
              CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 14. جدول model_has_roles
        DB::statement("
            CREATE TABLE IF NOT EXISTS `model_has_roles` (
              `role_id` bigint(20) unsigned NOT NULL,
              `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `model_id` int(10) NOT NULL,
              PRIMARY KEY (`role_id`,`model_id`,`model_type`),
              KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
              CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 15. جدول role_has_permissions
        DB::statement("
            CREATE TABLE IF NOT EXISTS `role_has_permissions` (
              `permission_id` bigint(20) unsigned NOT NULL,
              `role_id` bigint(20) unsigned NOT NULL,
              PRIMARY KEY (`permission_id`,`role_id`),
              KEY `role_has_permissions_role_id_foreign` (`role_id`),
              CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
              CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 16. جدول role_manageable_roles
        DB::statement("
            CREATE TABLE IF NOT EXISTS `role_manageable_roles` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `role_id` bigint(20) unsigned NOT NULL,
              `manageable_role_id` bigint(20) unsigned NOT NULL,
              `created_by` bigint(20) unsigned DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_role_manageable` (`role_id`,`manageable_role_id`),
              KEY `idx_role_id` (`role_id`),
              KEY `idx_manageable_role_id` (`manageable_role_id`),
              CONSTRAINT `fk_rmr_manageable_role` FOREIGN KEY (`manageable_role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
              CONSTRAINT `fk_rmr_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ادارة الادوار الوظيفية';
        ");

        // 17. جدول exports
        DB::statement("
            CREATE TABLE IF NOT EXISTS `exports` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `completed_at` timestamp NULL DEFAULT NULL,
              `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `processed_rows` int(10) unsigned NOT NULL DEFAULT 0,
              `total_rows` int(10) unsigned NOT NULL,
              `successful_rows` int(10) unsigned NOT NULL DEFAULT 0,
              `user_USER_IDENT` int(11) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 18. جدول imports
        DB::statement("
            CREATE TABLE IF NOT EXISTS `imports` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `completed_at` timestamp NULL DEFAULT NULL,
              `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `processed_rows` int(10) unsigned NOT NULL DEFAULT 0,
              `total_rows` int(10) unsigned NOT NULL,
              `successful_rows` int(10) unsigned NOT NULL DEFAULT 0,
              `user_USER_IDENT` int(11) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 19. جدول failed_import_rows
        DB::statement("
            CREATE TABLE IF NOT EXISTS `failed_import_rows` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `data` json NOT NULL,
              `import_id` bigint(20) unsigned NOT NULL,
              `validation_error` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `failed_import_rows_import_id_foreign` (`import_id`),
              CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 20. جدول activity_log
        DB::statement("
            CREATE TABLE IF NOT EXISTS `activity_log` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `log_name` varchar(255) DEFAULT NULL,
              `description` text NOT NULL,
              `subject_type` varchar(255) DEFAULT NULL,
              `event` varchar(255) DEFAULT NULL,
              `subject_id` bigint(20) unsigned DEFAULT NULL,
              `causer_type` varchar(255) DEFAULT NULL,
              `causer_id` bigint(20) unsigned DEFAULT NULL,
              `properties` json DEFAULT NULL,
              `batch_uuid` char(36) DEFAULT NULL,
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `activity_log_log_name_index` (`log_name`),
              KEY `subject_index` (`subject_type`,`subject_id`),
              KEY `causer_index` (`causer_type`,`causer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        echo "  [✓] تمت المعالجة بنجاح لقاعدة: {$dbName}\n";
    } catch (\Throwable $e) {
        echo "  [✗] خطأ في {$dbName}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== انتهت المعالجة لكافة قواعد البيانات ===\n";
