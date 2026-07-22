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
echo 'HTML length: '.strlen($html)."\n";
if (preg_match_all('/wire:key="([^"]+)"/', $html, $matches)) {
    // Print first 20 keys
    print_r(array_slice($matches[1], 0, 20));
}
