<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$class = new ReflectionClass(\Filament\Tables\Table::class);
if ($class->hasMethod('recordKey')) {
    echo 'YES';
} else {
    echo 'NO';
}
