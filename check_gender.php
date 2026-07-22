<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$genders = DB::table('applicant')->distinct()->pluck('GENDER')->toArray();
echo 'Genders: '.json_encode($genders, JSON_UNESCAPED_UNICODE)."\n";
