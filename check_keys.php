<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

$applicants = DB::connection('tenant')->table('applicant')->limit(5)->get();
$faculties = DB::connection('tenant')->table('faculty')->limit(5)->get();

echo "Applicants:\n";
foreach ($applicants as $a) {
    echo "UNID: {$a->UNID}, APPLICANT_IDENT: {$a->APPLICANT_IDENT}, FULL_NAME: {$a->FULL_NAME}\n";
}

echo "\nFaculties:\n";
foreach ($faculties as $f) {
    echo "UNID: {$f->UNID}, FACULTY_IDENT: {$f->FACULTY_IDENT}, FACULTY_NAME: {$f->FACULTY_NAME}\n";
}
