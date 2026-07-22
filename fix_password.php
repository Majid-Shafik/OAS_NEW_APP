<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$hashed = crypt('He-Ycit-321', '$1$somethin$');
DB::table('users')->where('LOGON_ID', 'ycit@gmail.com')->update(['LOGON_PASS' => $hashed]);

echo 'Password updated to: '.$hashed;
