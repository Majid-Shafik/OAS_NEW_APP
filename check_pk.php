<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Applicant;
use App\Models\Faculty;
use Illuminate\Contracts\Console\Kernel;

$f = Faculty::limit(3)->get();
foreach ($f as $row) {
    echo "Faculty PK ({$row->getKeyName()}): {$row->getKey()} \n";
}

$a = Applicant::limit(3)->get();
foreach ($a as $row) {
    echo "Applicant PK ({$row->getKeyName()}): {$row->getKey()} \n";
}
