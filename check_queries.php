<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Resources\Applicants\ApplicantResource;
use App\Filament\Resources\Applicants\Tables\ApplicantsTable;
use App\Models\Applicant;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

// We just want to dump Applicant::query()->paginate(5) using the same DB settings.
config(['database.connections.tenant.database' => 'p_oas_db_2022']);
DB::purge('tenant');
DB::setDefaultConnection('tenant');

DB::enableQueryLog();
$records = Applicant::query()->paginate(5);
$queries = DB::getQueryLog();

foreach ($records as $r) {
    echo "ID: " . $r->getKey() . " Name: " . $r->FULL_NAME . "\n";
}

print_r($queries);
