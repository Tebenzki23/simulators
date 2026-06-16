<?php
$host = '127.0.0.1';
$db   = 'windows69';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If the database doesn't exist yet, we will handle it in init_db.php
    // In production, we would log this instead of outputting
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        // Suppress so init_db.php can still run by catching it there
    } else {
        die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]));
    }
}
?>
