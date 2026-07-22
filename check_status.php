<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$applicantStatuses = DB::table('applicant')->distinct()->pluck('STATUS')->toArray();
echo 'Applicant Statuses: '.json_encode($applicantStatuses, JSON_UNESCAPED_UNICODE)."\n";
