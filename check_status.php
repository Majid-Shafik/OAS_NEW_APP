<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$applicantStatuses = \Illuminate\Support\Facades\DB::table('applicant')->distinct()->pluck('STATUS')->toArray();
echo "Applicant Statuses: " . json_encode($applicantStatuses, JSON_UNESCAPED_UNICODE) . "\n";
