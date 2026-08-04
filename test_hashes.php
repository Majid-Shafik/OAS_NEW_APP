?php

$pass1 = '$1$somethin$MXDLwPEBVWdGd7a1xgbfP/';
$pass2 = '$1$somethin$oNiLLjNbUZsTOBGj2O6ux0';

$testWords = [
    'He-Ycit-321',
    'he-ycit-321',
    '123456',
    '12345678',
    'admin',
    'password',
    'root',
    'ycit@gmail.com',
    'ycit',
    '123456789',
    '777777777',
    '0',
    '55562',
];

$results = [];
foreach ($testWords as $w) {
    $c = crypt($w, '$1$somethin$');
    $results[$w] = [
        'crypt' => $c,
        'matches_pass1' => ($c === $pass1),
        'matches_pass2' => ($c === $pass2),
    ];
}

echo json_encode([
    'pass1' => $pass1,
    'pass2' => $pass2,
    'results' => $results,
], JSON_PRETTY_PRINT);
