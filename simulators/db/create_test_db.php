<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $dbname = 'test_database';
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Create a table
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert some queries (records)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_table");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO test_table (name, description) VALUES (?, ?)");
        $data = [
            ['Query 1', 'This is the first test query inserted.'],
            ['Query 2', 'This is another record.'],
            ['Sample Entry', 'Testing the simulator database.'],
            ['Offline Mode', 'Testing offline mode data insertion.']
        ];
        foreach ($data as $row) {
            $insert->execute($row);
        }
    }

    echo "Database '$dbname' created successfully, and queries inserted!\n";
} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
