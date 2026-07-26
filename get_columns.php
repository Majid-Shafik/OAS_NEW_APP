<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $columns = Illuminate\Support\Facades\Schema::getColumnListing('general_standards');
    if (empty($columns)) {
        // Fallback for some DB drivers
        $first = Illuminate\Support\Facades\DB::table('general_standards')->first();
        if ($first) {
            $columns = array_keys((array) $first);
        }
    }
    echo "COLUMNS:\n";
    print_r($columns);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
