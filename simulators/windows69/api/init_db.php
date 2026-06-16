<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS windows69 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE windows69");

    // Table: files
    $pdo->exec("CREATE TABLE IF NOT EXISTS files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_path VARCHAR(500) NOT NULL DEFAULT '',
        name VARCHAR(255) NOT NULL,
        is_dir TINYINT(1) DEFAULT 0,
        content LONGBLOB,
        size INT DEFAULT 0,
        modified INT DEFAULT 0,
        is_deleted TINYINT(1) DEFAULT 0,
        UNIQUE KEY unique_file (parent_path, name, is_deleted)
    )");

    // Table: recycle_bin
    $pdo->exec("CREATE TABLE IF NOT EXISTS recycle_bin (
        uuid VARCHAR(50) PRIMARY KEY,
        file_id INT NOT NULL,
        original_parent VARCHAR(500) NOT NULL,
        delete_date INT NOT NULL,
        drive VARCHAR(50) DEFAULT 'Drive_C'
    )");

    // Table: settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        key_name VARCHAR(100) PRIMARY KEY,
        value LONGTEXT
    )");

    echo "Database and tables created successfully!\n";

} catch (PDOException $e) {
    die("DB Init Error: " . $e->getMessage() . "\n");
}
?>
