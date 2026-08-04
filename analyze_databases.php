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

// 1. Get all available databases in MySQL
$allDbs = DB::select("SHOW DATABASES");
$oasDbs = [];
foreach ($allDbs as $dbObj) {
    $dbName = $dbObj->Database;
    if (str_starts_with($dbName, 'p_oas_db_')) {
        $oasDbs[] = $dbName;
    }
}
sort($oasDbs);

// 2. Discover all tables used by Models in app/Models
$modelFiles = glob(__DIR__.'/app/Models/*.php');
$activeTables = [];
$modelTableMap = [];

foreach ($modelFiles as $file) {
    $className = 'App\\Models\\' . basename($file, '.php');
    if (class_exists($className)) {
        try {
            $ref = new ReflectionClass($className);
            if (!$ref->isAbstract() && $ref->isSubclassOf(\Illuminate\Database\Eloquent\Model::class)) {
                $instance = new $className;
                $table = $instance->getTable();
                $activeTables[$table] = $className;
                $modelTableMap[$table] = class_basename($className);
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }
}

// Add system framework tables
$frameworkTables = [
    'users', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
    'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
    'role_manageable_roles', 'exports', 'imports', 'failed_import_rows', 'activity_log'
];
foreach ($frameworkTables as $ft) {
    if (!isset($activeTables[$ft])) {
        $activeTables[$ft] = 'System/Framework';
        $modelTableMap[$ft] = $ft;
    }
}

// Additional tables commonly queried directly or via joins
$extraTables = [
    'combo_boxes', 'combo_values', 'province', 'country', 'study_type', 'payment_methods',
    'applicant_attachment', 'app_bill_ident_canceled', 'application_group', 'program_capacity_history'
];
foreach ($extraTables as $et) {
    if (!isset($activeTables[$et])) {
        $activeTables[$et] = 'Reference/Join';
        $modelTableMap[$et] = $et;
    }
}

// 3. Fetch schema of reference DB: p_oas_db_2022
$refDb = 'p_oas_db_2022';
Config::set('database.connections.ref_db', array_merge(config('database.connections.mysql'), ['database' => $refDb]));
DB::purge('ref_db');

$refSchema = [];
foreach (array_keys($activeTables) as $table) {
    $hasTable = !empty(DB::connection('ref_db')->select("SHOW TABLES LIKE '{$table}'"));
    if ($hasTable) {
        $cols = DB::connection('ref_db')->select("SHOW FULL COLUMNS FROM `{$table}`");
        $colDetails = [];
        foreach ($cols as $col) {
            $colDetails[$col->Field] = [
                'type' => $col->Type,
                'null' => $col->Null,
                'key' => $col->Key,
                'default' => $col->Default,
                'extra' => $col->Extra,
                'comment' => $col->Comment,
            ];
        }
        $refSchema[$table] = $colDetails;
    }
}

// 4. Compare every database with refSchema
$comparisonResults = [];

foreach ($oasDbs as $targetDb) {
    Config::set("database.connections.target_{$targetDb}", array_merge(config('database.connections.mysql'), ['database' => $targetDb]));
    DB::purge("target_{$targetDb}");
    $conn = DB::connection("target_{$targetDb}");

    $dbResult = [
        'database' => $targetDb,
        'missing_tables' => [],
        'existing_tables_count' => 0,
        'total_active_tables' => count($activeTables),
        'table_differences' => [],
        'ready_for_production' => true,
        'required_migrations' => [],
    ];

    foreach (array_keys($activeTables) as $table) {
        $tableExists = !empty($conn->select("SHOW TABLES LIKE '{$table}'"));
        if (!$tableExists) {
            $dbResult['missing_tables'][] = $table;
            $dbResult['ready_for_production'] = false;
            $dbResult['required_migrations'][] = "CREATE TABLE `{$table}` (Missing table)";
            continue;
        }

        $dbResult['existing_tables_count']++;

        if (isset($refSchema[$table])) {
            $targetCols = $conn->select("SHOW FULL COLUMNS FROM `{$table}`");
            $targetColDetails = [];
            foreach ($targetCols as $col) {
                $targetColDetails[$col->Field] = [
                    'type' => $col->Type,
                    'null' => $col->Null,
                    'key' => $col->Key,
                    'default' => $col->Default,
                    'extra' => $col->Extra,
                ];
            }

            $missingCols = [];
            $differingCols = [];

            foreach ($refSchema[$table] as $field => $refDetails) {
                if (!isset($targetColDetails[$field])) {
                    $missingCols[] = [
                        'column' => $field,
                        'expected_type' => $refDetails['type'],
                        'null' => $refDetails['null'],
                        'default' => $refDetails['default'],
                    ];
                    $dbResult['required_migrations'][] = "ALTER TABLE `{$table}` ADD COLUMN `{$field}` {$refDetails['type']} " . ($refDetails['null'] === 'YES' ? 'NULL' : 'NOT NULL');
                } else {
                    $targetType = $targetColDetails[$field]['type'];
                    $refType = $refDetails['type'];
                    // Check significant type differences
                    if (strtolower($targetType) !== strtolower($refType)) {
                        // Ignore minor collation differences if basic type is same
                        $differingCols[] = [
                            'column' => $field,
                            'ref_type' => $refType,
                            'target_type' => $targetType,
                        ];
                    }
                }
            }

            if (!empty($missingCols) || !empty($differingCols)) {
                $dbResult['table_differences'][$table] = [
                    'missing_columns' => $missingCols,
                    'differing_columns' => $differingCols,
                ];
                if (!empty($missingCols)) {
                    $dbResult['ready_for_production'] = false;
                }
            }
        }
    }

    $comparisonResults[$targetDb] = $dbResult;
}

// 5. Save report data
$report = [
    'generated_at' => date('Y-m-d H:i:s'),
    'reference_database' => $refDb,
    'total_active_tables_checked' => count($activeTables),
    'active_tables_list' => $modelTableMap,
    'available_databases' => $oasDbs,
    'database_comparisons' => $comparisonResults,
];

file_put_contents(__DIR__.'/db_analysis_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'status' => 'success',
    'available_databases' => $oasDbs,
    'total_active_tables' => count($activeTables),
    'databases_summary' => array_map(function($res) {
        return [
            'database' => $res['database'],
            'existing_tables' => $res['existing_tables_count'],
            'missing_tables_count' => count($res['missing_tables']),
            'missing_tables' => $res['missing_tables'],
            'tables_with_diffs' => count($res['table_differences']),
            'required_migrations_count' => count($res['required_migrations']),
        ];
    }, $comparisonResults),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
