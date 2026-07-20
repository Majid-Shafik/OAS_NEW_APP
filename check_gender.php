<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$genders = \Illuminate\Support\Facades\DB::table('applicant')->distinct()->pluck('GENDER')->toArray();
echo "Genders: " . json_encode($genders, JSON_UNESCAPED_UNICODE) . "\n";
