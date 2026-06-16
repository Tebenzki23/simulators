<?php
require_once 'db.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$key = $_GET['key'] ?? $_POST['key'] ?? '';

if (!$action || !$key) {
    echo json_encode(['success' => false, 'error' => 'Missing action or key']);
    exit;
}

if ($action === 'get') {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    
    if ($row) {
        echo json_encode(['success' => true, 'value' => json_decode($row['value'], true)]);
    } else {
        echo json_encode(['success' => true, 'value' => null]);
    }
} elseif ($action === 'set') {
    $value = $_POST['value'] ?? '';
    // Expected value is a JSON string
    
    $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
    if ($stmt->execute([$key, $value, $value])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save settings']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>
