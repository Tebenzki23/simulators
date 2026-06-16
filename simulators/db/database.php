<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'xampp_sim_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Return json error since this will usually be included in an API request
    echo json_encode(['error' => 'Database connection failed. Please run setup.php first.']);
    exit;
}
?>
