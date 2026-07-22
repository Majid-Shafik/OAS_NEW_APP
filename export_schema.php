<?php

$host = '127.0.0.1';
$db = 'p_oas_db_2022';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    $tablesQuery = $pdo->query('SHOW TABLES');
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);

    $schema = [];
    foreach ($tables as $table) {
        $columnsQuery = $pdo->query("SHOW COLUMNS FROM `$table`");
        $columns = $columnsQuery->fetchAll();
        $schema[$table] = $columns;
    }

    file_put_contents('schema_2022.json', json_encode($schema, JSON_PRETTY_PRINT));
    echo "Schema exported successfully.\n";
} catch (PDOException $e) {
    echo 'Error: '.$e->getMessage();
}
