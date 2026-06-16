<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'xampp_sim_db';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    $pdo->exec("USE `$dbname`");

    // Create modules table
    $pdo->exec("CREATE TABLE IF NOT EXISTS modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        status VARCHAR(20) DEFAULT 'stopped',
        pid VARCHAR(20) DEFAULT '',
        port VARCHAR(50) DEFAULT ''
    )");

    // Create logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        message TEXT NOT NULL
    )");

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        stock INT DEFAULT 0,
        category VARCHAR(50)
    )");

    // Create orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total DECIMAL(10, 2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        ordered_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed modules
    $modules = [
        ['Apache', 'stopped', '', '80, 443'],
        ['MySQL', 'stopped', '', '3306'],
        ['FileZilla', 'stopped', '', '21, 14147'],
        ['Mercury', 'stopped', '', '25, 79, 110, 143, 2224'],
        ['Tomcat', 'stopped', '', '8080, 8005, 8009']
    ];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO modules (name, status, pid, port) VALUES (?, ?, ?, ?)");
        foreach ($modules as $mod) {
            $insert->execute($mod);
        }
    }

    // Seed users
    $users = [
        ['admin', 'admin@example.com', 'admin'],
        ['john_doe', 'john@example.com', 'user'],
        ['jane_smith', 'jane@example.com', 'user'],
        ['bob_builder', 'bob@example.com', 'editor'],
        ['alice_w', 'alice@example.com', 'user']
    ];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO users (username, email, role) VALUES (?, ?, ?)");
        foreach ($users as $u) {
            $insert->execute($u);
        }
    }

    // Seed products
    $products = [
        ['Gaming Laptop', 1299.99, 15, 'Electronics'],
        ['Wireless Mouse', 29.99, 120, 'Accessories'],
        ['Mechanical Keyboard', 89.99, 45, 'Accessories'],
        ['27-inch Monitor', 249.99, 30, 'Electronics'],
        ['Bluetooth Speaker', 59.99, 60, 'Audio']
    ];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO products (name, price, stock, category) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $insert->execute($p);
        }
    }

    // Seed orders
    $orders = [
        [2, 1329.98, 'completed'],
        [3, 89.99, 'shipped'],
        [5, 59.99, 'pending']
    ];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)");
        foreach ($orders as $o) {
            $insert->execute($o);
        }
    }

    echo "Database and tables created successfully! Modules, Users, Products, and Orders seeded. You can now go to index.php.";
} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
