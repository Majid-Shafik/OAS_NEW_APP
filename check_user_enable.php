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

$u = User::where('LOGON_ID', 'admin')->orWhere('LOGON_ID', 'Admin')->first(); // Assuming they are using admin
if ($u) {
    echo "USER_NAME: {$u->USER_NAME}\n";
    echo "IS_IT_ENABLE: " . var_export($u->IS_IT_ENABLE, true) . " (Type: " . gettype($u->IS_IT_ENABLE) . ")\n";
} else {
    echo "User not found\n";
    // Get first user
    $u = User::first();
    echo "First user LOGON_ID: {$u->LOGON_ID}\n";
    echo "First user IS_IT_ENABLE: " . var_export($u->IS_IT_ENABLE, true) . " (Type: " . gettype($u->IS_IT_ENABLE) . ")\n";
}
