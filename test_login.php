<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Pages\Auth\CustomLogin;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;

// Simulate the CustomLogin component
$component = Livewire::test(CustomLogin::class)
    ->fillForm([
        'LOGON_ID' => 'admin@gmail.com',
        'password' => 'admin@123', // I don't know the real password, let's just trace
        'database' => 'p_oas_db_2022'
    ])
    ->call('authenticate');

print_r($component->errors());
