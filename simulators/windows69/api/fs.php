<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$path = $_POST['path'] ?? $_GET['path'] ?? '';

// Basic security check
if (strpos($path, '..') !== false) {
    echo json_encode(['success' => false, 'error' => 'Invalid path']);
    exit;
}

// Helper to split path into parent and name
function split_path($path) {
    $path = trim($path, '/');
    if ($path === '') return ['', ''];
    $parts = explode('/', $path);
    $name = array_pop($parts);
    $parent = implode('/', $parts);
    return [$parent, $name];
}

// Helper to ensure parent directories exist
function ensure_dir_exists($pdo, $path) {
    if (empty($path)) return;
    $parts = explode('/', $path);
    $current_parent = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $stmt = $pdo->prepare("INSERT IGNORE INTO files (parent_path, name, is_dir, modified) VALUES (?, ?, 1, ?)");
        $stmt->execute([$current_parent, $part, time()]);
        $current_parent = $current_parent === '' ? $part : $current_parent . '/' . $part;
    }
}

if ($action === 'list') {
    // Standard folders initialization
    $standard_folders = ['Desktop', 'Documents', 'Downloads', 'Music', 'Pictures', 'Videos', 'Drive_C', 'Drive_D', 'Drive_USB'];
    if (empty($path)) {
        foreach ($standard_folders as $sf) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO files (parent_path, name, is_dir, modified) VALUES ('', ?, 1, ?)");
            $stmt->execute([$sf, time()]);
        }
    }

    $clean_path = trim($path, '/');
    $stmt = $pdo->prepare("SELECT id, name, is_dir, size, modified FROM files WHERE parent_path = ? AND is_deleted = 0 ORDER BY is_dir DESC, name ASC");
    $stmt->execute([$clean_path]);
    $files = $stmt->fetchAll();

    $result = [];
    foreach ($files as $f) {
        $result[] = [
            'name' => $f['name'],
            'is_dir' => (bool)$f['is_dir'],
            'size' => (int)$f['size'],
            'modified' => (int)$f['modified']
        ];
    }
    echo json_encode(['success' => true, 'files' => $result]);

} else if ($action === 'read') {
    [$parent, $name] = split_path($path);
    $stmt = $pdo->prepare("SELECT content, is_dir FROM files WHERE parent_path = ? AND name = ? AND is_deleted = 0");
    $stmt->execute([$parent, $name]);
    $file = $stmt->fetch();

    if ($file && !$file['is_dir']) {
        echo json_encode(['success' => true, 'content' => $file['content']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'File not found']);
    }

} else if ($action === 'write') {
    $content = $_POST['content'] ?? '';
    [$parent, $name] = split_path($path);
    
    // Ensure parent exists
    ensure_dir_exists($pdo, $parent);

    $size = strlen($content);
    $modified = time();

    $stmt = $pdo->prepare("
        INSERT INTO files (parent_path, name, is_dir, content, size, modified, is_deleted) 
        VALUES (?, ?, 0, ?, ?, ?, 0) 
        ON DUPLICATE KEY UPDATE content = VALUES(content), size = VALUES(size), modified = VALUES(modified), is_deleted = 0
    ");
    
    if ($stmt->execute([$parent, $name, $content, $size, $modified])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write']);
    }

} else if ($action === 'delete') {
    $permanent = isset($_POST['permanent']) ? filter_var($_POST['permanent'], FILTER_VALIDATE_BOOLEAN) : false;
    $clean_path = trim($path, '/');
    [$parent, $name] = split_path($clean_path);

    // Check recycle bin config
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'recycle_config'");
    $stmt->execute();
    $configRow = $stmt->fetch();
    $config = ['limitMB' => 100, 'bypassRecycleBin' => false];
    if ($configRow && $configRow['value']) {
        $config = json_decode($configRow['value'], true);
    }
    if ($config['bypassRecycleBin']) {
        $permanent = true;
    }

    $drive = '';
    $path_parts = explode('/', $clean_path);
    if (count($path_parts) > 0 && strpos($path_parts[0], 'Drive_') === 0) {
        $drive = $path_parts[0];
    }
    if ($drive === 'Drive_USB' || $drive === 'Drive_CDROM') {
        $permanent = true;
    }

    $stmt = $pdo->prepare("SELECT id, is_dir, size FROM files WHERE parent_path = ? AND name = ? AND is_deleted = 0");
    $stmt->execute([$parent, $name]);
    $file = $stmt->fetch();

    if (!$file) {
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }

    if ($permanent) {
        if ($file['is_dir']) {
            // Delete folder and all contents
            $prefix = $clean_path . '/%';
            $pdo->prepare("DELETE FROM files WHERE parent_path LIKE ? OR parent_path = ?")->execute([$prefix, $clean_path]);
        }
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$file['id']]);
        echo json_encode(['success' => true]);
    } else {
        if (!$drive) $drive = 'Drive_C';
        $uuid = uniqid('rb_');
        
        $pdo->beginTransaction();
        try {
            // Update the item and its children to be deleted
            $pdo->prepare("UPDATE files SET is_deleted = 1 WHERE id = ?")->execute([$file['id']]);
            if ($file['is_dir']) {
                $prefix = $clean_path . '/%';
                $pdo->prepare("UPDATE files SET is_deleted = 1 WHERE parent_path LIKE ? OR parent_path = ?")->execute([$prefix, $clean_path]);
            }
            
            // Calculate total size
            $total_size = $file['size'];
            if ($file['is_dir']) {
                $prefix = $clean_path . '/%';
                $size_stmt = $pdo->prepare("SELECT SUM(size) as total FROM files WHERE parent_path LIKE ? OR parent_path = ?");
                $size_stmt->execute([$prefix, $clean_path]);
                $total_size += (int)$size_stmt->fetch()['total'];
            }

            // Insert into recycle bin
            $pdo->prepare("INSERT INTO recycle_bin (uuid, file_id, original_parent, delete_date, drive) VALUES (?, ?, ?, ?, ?)")
                ->execute([$uuid, $file['id'], $parent, time(), $drive]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Failed to move to Recycle Bin: ' . $e->getMessage()]);
        }
    }

} else if ($action === 'restore_bin') {
    $uuid = $_POST['uuid'] ?? '';
    
    $stmt = $pdo->prepare("SELECT rb.*, f.name, f.is_dir, f.parent_path AS current_parent FROM recycle_bin rb JOIN files f ON rb.file_id = f.id WHERE rb.uuid = ?");
    $stmt->execute([$uuid]);
    $item = $stmt->fetch();

    if (!$item) {
        echo json_encode(['success' => false, 'error' => 'Item not found in Recycle Bin']);
        exit;
    }

    $pdo->beginTransaction();
    try {
        // Ensure original parent still exists (if it doesn't, we will just restore to root or recreate the path)
        ensure_dir_exists($pdo, $item['original_parent']);
        
        $clean_path = $item['current_parent'] ? $item['current_parent'] . '/' . $item['name'] : $item['name'];

        // Restore item
        $pdo->prepare("UPDATE files SET is_deleted = 0 WHERE id = ?")->execute([$item['file_id']]);
        
        // Restore children if it's a directory
        if ($item['is_dir']) {
            $prefix = $clean_path . '/%';
            $pdo->prepare("UPDATE files SET is_deleted = 0 WHERE parent_path LIKE ? OR parent_path = ?")->execute([$prefix, $clean_path]);
        }

        // Remove from recycle_bin table
        $pdo->prepare("DELETE FROM recycle_bin WHERE uuid = ?")->execute([$uuid]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Restore failed: ' . $e->getMessage()]);
    }

} else if ($action === 'empty_bin') {
    $drive = $_POST['drive'] ?? '';
    $params = [];
    $query = "SELECT rb.uuid, f.id, f.is_dir, f.name, f.parent_path FROM recycle_bin rb JOIN files f ON rb.file_id = f.id";
    
    if ($drive) {
        $query .= " WHERE rb.drive = ?";
        $params[] = $drive;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        $clean_path = $item['parent_path'] ? $item['parent_path'] . '/' . $item['name'] : $item['name'];
        if ($item['is_dir']) {
            $prefix = $clean_path . '/%';
            $pdo->prepare("DELETE FROM files WHERE parent_path LIKE ? OR parent_path = ?")->execute([$prefix, $clean_path]);
        }
        $pdo->prepare("DELETE FROM files WHERE id = ?")->execute([$item['id']]);
        $pdo->prepare("DELETE FROM recycle_bin WHERE uuid = ?")->execute([$item['uuid']]);
    }

    echo json_encode(['success' => true]);

} else if ($action === 'bin_config') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $config = [
            'limitMB' => (int)($_POST['limitMB'] ?? 100),
            'bypassRecycleBin' => filter_var($_POST['bypassRecycleBin'] ?? false, FILTER_VALIDATE_BOOLEAN)
        ];
        $val = json_encode($config);
        $stmt = $pdo->prepare("INSERT INTO settings (key_name, value) VALUES ('recycle_config', ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$val, $val]);
        echo json_encode(['success' => true, 'config' => $config]);
    } else {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE key_name = 'recycle_config'");
        $stmt->execute();
        $row = $stmt->fetch();
        $config = ['limitMB' => 100, 'bypassRecycleBin' => false];
        if ($row && $row['value']) {
            $config = json_decode($row['value'], true);
        }
        echo json_encode(['success' => true, 'config' => $config]);
    }

} else if ($action === 'get_bin_items') {
    $drive = $_POST['drive'] ?? '';
    $params = [];
    $query = "SELECT rb.uuid, rb.drive, rb.delete_date, rb.original_parent, f.name, f.is_dir, f.size FROM recycle_bin rb JOIN files f ON rb.file_id = f.id";
    if ($drive) {
        $query .= " WHERE rb.drive = ?";
        $params[] = $drive;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $row) {
        $items[] = [
            'uuid' => $row['uuid'],
            'drive' => $row['drive'],
            'originalName' => $row['name'],
            'originalPath' => $row['original_parent'] ? $row['original_parent'] . '/' . $row['name'] : $row['name'],
            'dateDeleted' => (int)$row['delete_date'],
            'size' => (int)$row['size'],
            'is_dir' => (bool)$row['is_dir']
        ];
    }
    echo json_encode(['success' => true, 'items' => $items]);

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
?>
