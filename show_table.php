<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::connection('tenant')->getColumnListing('app_bill_ident_canceled');
echo json_encode($columns, JSON_PRETTY_PRINT);
