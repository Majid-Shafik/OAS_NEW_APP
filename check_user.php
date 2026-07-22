<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

$u = User::first();
print_r(array_keys($u->getAttributes()));
