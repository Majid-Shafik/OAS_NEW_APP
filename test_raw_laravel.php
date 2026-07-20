<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

$rows = DB::select('select UNID, APPLICANT_IDENT, FULL_NAME from `applicant` order by `applicant`.`APPLICANT_IDENT` asc limit 10 offset 0');
foreach($rows as $row) {
    echo "UNID: {$row->UNID}, APPLICANT_IDENT: {$row->APPLICANT_IDENT}, FULL_NAME: {$row->FULL_NAME}\n";
}
