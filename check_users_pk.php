<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

$rows = DB::select('select UNID, USER_IDENT, LOGON_ID, IS_IT_ENABLE from `users` order by `users`.`USER_IDENT` asc limit 10 offset 0');
foreach($rows as $row) {
    echo "UNID: {$row->UNID}, USER_IDENT: {$row->USER_IDENT}, LOGON_ID: {$row->LOGON_ID}, IS_IT_ENABLE: {$row->IS_IT_ENABLE}\n";
}
