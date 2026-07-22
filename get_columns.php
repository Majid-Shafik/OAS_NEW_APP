<?php

$schema = json_decode(file_get_contents(__DIR__.'/schema_2022.json'), true);
$tables = ['applicant', 'faculty', 'programs'];
$result = [];
foreach ($tables as $table) {
    $result[$table] = array_column($schema[$table], 'Field');
}
file_put_contents(__DIR__.'/columns.json', json_encode($result, JSON_PRETTY_PRINT));
echo 'Columns extracted.';
