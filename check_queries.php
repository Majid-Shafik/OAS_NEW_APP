<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Applicant;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// We just want to dump Applicant::query()->paginate(5) using the same DB settings.
config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

DB::enableQueryLog();
$records = Applicant::query()->paginate(5);
$queries = DB::getQueryLog();

foreach ($records as $r) {
    echo 'ID: '.$r->getKey().' Name: '.$r->FULL_NAME."\n";
}

print_r($queries);
