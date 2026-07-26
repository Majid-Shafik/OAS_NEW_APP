<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$offerings = Schema::getColumnListing('offerings');
$programs = Schema::getColumnListing('programs');
$faculties = Schema::getColumnListing('faculties');

file_put_contents(__DIR__.'/schema_dump.json', json_encode([
    'offerings' => $offerings,
    'programs' => $programs,
    'faculties' => $faculties
], JSON_PRETTY_PRINT));
