<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Illuminate\Support\Facades\DB::statement('CREATE TABLE IF NOT EXISTS deleted_applications LIKE applications');
    echo "Table deleted_applications created successfully or already exists.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
