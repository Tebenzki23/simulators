<?php
session_start();
header('Content-Type: application/json');

// Mock user store
$users = [
    'admin' => 'password',
    'guest' => ''
];

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $password = $_POST['password'] ?? '';
    // Simplify: any password == 'admin' or 'password' lets admin in.
    if ($password === 'admin' || $password === 'password') {
        $_SESSION['user'] = 'admin';
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect password (try: admin or password)']);
    }
} else if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
} else if ($action === 'status') {
    echo json_encode(['authenticated' => isset($_SESSION['user'])]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
