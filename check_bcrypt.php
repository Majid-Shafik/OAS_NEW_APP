<?php

require_once __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;

$bcryptHash = '$2y$12$uLHOGImzDL2tJGN3/aVCruglWYD7CIjBtpEuhcHWLm0fC3WCIlIYy';

$candidates = [
    'He-Ycit-321',
    'he-ycit-321',
    '123456',
    '12345678',
    '123456789',
    'admin',
    'password',
    'root',
    'ycit@gmail.com',
    'ycit',
    '777777777',
    '0',
    '55562',
    'admin123',
    'password123',
    'secret',
];

$matched = null;
foreach ($candidates as $c) {
    if (Hash::check($c, $bcryptHash)) {
        $matched = $c;
        break;
    }
}

echo json_encode([
    'hash' => $bcryptHash,
    'matched_password' => $matched,
], JSON_PRETTY_PRINT);
