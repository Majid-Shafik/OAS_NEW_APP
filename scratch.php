<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$values = \App\Models\ComboValue::where('COMBO_CODE', 7)->get()->toArray();
echo json_encode($values, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
