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

$json = file_get_contents(__DIR__.'/db_analysis_report.json');
$report = json_decode($json, true);

// Focus on 2016, 2017, 2018
$years = ['p_oas_db_2016', 'p_oas_db_2017', 'p_oas_db_2018', 'p_oas_db_2019', 'p_oas_db_2020', 'p_oas_db_2021', 'p_oas_db_2022'];

$detailedReport = [];

foreach ($years as $db) {
    if (!isset($report['database_comparisons'][$db])) continue;
    $comp = $report['database_comparisons'][$db];
    
    $detailedReport[$db] = [
        'missing_tables' => $comp['missing_tables'],
        'tables_with_missing_columns' => [],
    ];
    
    foreach ($comp['table_differences'] as $tbl => $diff) {
        if (!empty($diff['missing_columns'])) {
            $detailedReport[$db]['tables_with_missing_columns'][$tbl] = array_map(function($c) {
                return $c['column'] . ' (' . $c['expected_type'] . ')';
            }, $diff['missing_columns']);
        }
    }
}

file_put_contents(__DIR__.'/detailed_differences.json', json_encode($detailedReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode($detailedReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
