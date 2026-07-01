<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', 'root');
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `sfd_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database 'sfd_portal' created successfully!\n";
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
}
