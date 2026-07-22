<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

$html = Livewire::mount('app.filament.resources.applicants.pages.list-applicants');
if (preg_match_all('/wire:key="([^"]+table\.records\.[^"]+)"/', $html, $matches)) {
    // Print all matches that are JUST the record wrapper (no column specifics)
    $records = array_filter($matches[1], function ($v) {
        return preg_match('/\.table\.records\.[^\.]+$/', $v);
    });
    print_r(array_values($records));
}
