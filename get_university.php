<?php
$schema = json_decode(file_get_contents(__DIR__ . '/schema_2022.json'), true);
echo json_encode(array_column($schema['university'], 'Field'), JSON_PRETTY_PRINT);
