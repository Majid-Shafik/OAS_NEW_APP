<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

$databases = [
    'p_oas_db_2022',
    'p_oas_db_2021',
    'p_oas_db_2020',
    'p_oas_db_2019',
    'p_oas_db_2018',
];

foreach ($databases as $dbName) {
    try {
        Config::set('database.connections.tenant.database', $dbName);
        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        DB::statement("ALTER TABLE `users` CHANGE COLUMN `LOGON_PASS` `LOGON_PASS` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8mb4_general_ci' AFTER `LOGON_ID`");
        echo "Successfully updated LOGON_PASS length in database: {$dbName}\n";
    } catch (Exception $e) {
        echo "Error updating {$dbName}: ".$e->getMessage()."\n";
    }
}
