<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3306', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS registration");
    echo "Database created or exists.\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
    exit(1);
}
