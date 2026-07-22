<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Applicant;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Temporarily set tenant DB
config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

$applicants = Applicant::limit(5)->get();

foreach ($applicants as $a) {
    echo "Model PK ({$a->getKeyName()}): {$a->getKey()}, UNID: {$a->UNID}, Name: {$a->FULL_NAME}\n";
}
