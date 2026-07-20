<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Applicant;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

DB::enableQueryLog();
$html = Livewire::mount('app.filament.resources.applicants.pages.list-applicants');
print_r(DB::getQueryLog());
