<?php

use Filament\Tables\Table;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$class = new ReflectionClass(Table::class);
if ($class->hasMethod('recordKey')) {
    echo 'YES';
} else {
    echo 'NO';
}
