<?php

use App\Models\RequestAdjustOffering;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$record = RequestAdjustOffering::first();
if ($record) {
    echo 'DB Name: '.DB::connection()->getDatabaseName()."\n";
    echo 'Base Path: '.$record->getBaseAttachmentPath()."\n";
    echo 'UN Path: '.$record->getUnAttachmentPath()."\n";
    echo 'File Exists: '.(file_exists($record->getUnAttachmentPath()) ? 'Yes' : 'No')."\n";

    // Let's test a specific request ID that we know exists from our previous list_dir, e.g. req_100.pdf
    $record100 = RequestAdjustOffering::find(100);
    if ($record100) {
        echo 'UN Path (req_100): '.$record100->getUnAttachmentPath()."\n";
        echo 'File Exists (req_100): '.(file_exists($record100->getUnAttachmentPath()) ? 'Yes' : 'No')."\n";
    }
} else {
    echo "No records found.\n";
}
