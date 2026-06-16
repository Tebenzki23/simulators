<?php
require_once 'database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Helper to validate identifier names (DB, Table, Column) to prevent SQL Injection
function validateIdentifier($name) {
    return preg_match('/^[a-zA-Z0-9_]+$/', $name) ? $name : null;
}

// 1. Status and Logs (existing)
if ($action === 'status') {
    $stmt = $pdo->query("SELECT * FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->query("SELECT * FROM logs ORDER BY id DESC LIMIT 50");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'modules' => $modules,
        'logs' => array_reverse($logs)
    ]);
    exit;
}

// 2. Toggle module status (existing but enhanced)
if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $status = $_POST['status'] ?? ''; // 'running' or 'stopped'
    
    if ($name && $status) {
        $pid = ($status === 'running') ? rand(1000, 9999) : '';
        
        $stmt = $pdo->prepare("UPDATE modules SET status = ?, pid = ? WHERE name = ?");
        $stmt->execute([$status, $pid, $name]);
        
        if ($status === 'running') {
             $logMsg = "Attempting to start $name app...\n$name started. [PID: $pid]";
        } else {
             $logMsg = "Status change detected: $name stopped.";
        }
        
        $stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");
        $stmt->execute([$logMsg]);
        
        echo json_encode(['success' => true]);
        exit;
    }
}

// 3. Log a custom message (existing)
if ($action === 'log' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'] ?? '';
    if ($message) {
        $stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");
        $stmt->execute([$message]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// NEW ENDPOINTS FOR 40% UPGRADE

// 4. Databases List
if ($action === 'databases') {
    try {
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'databases' => $databases]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 5. Tables List inside a DB
if ($action === 'tables') {
    $db = $_GET['db'] ?? '';
    $validatedDb = validateIdentifier($db);
    if (!$validatedDb) {
        echo json_encode(['success' => false, 'error' => 'Invalid database name']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'tables' => $tables]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 6. Table Structure
if ($action === 'table_structure') {
    $db = $_GET['db'] ?? '';
    $table = $_GET['table'] ?? '';
    
    $validatedDb = validateIdentifier($db);
    $validatedTable = validateIdentifier($table);
    
    if (!$validatedDb || !$validatedTable) {
        echo json_encode(['success' => false, 'error' => 'Invalid DB or Table name']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        $stmt = $pdo->query("DESCRIBE `$validatedTable`");
        $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'structure' => $structure]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 7. Table Data
if ($action === 'table_data') {
    $db = $_GET['db'] ?? '';
    $table = $_GET['table'] ?? '';
    
    $validatedDb = validateIdentifier($db);
    $validatedTable = validateIdentifier($table);
    
    if (!$validatedDb || !$validatedTable) {
        echo json_encode(['success' => false, 'error' => 'Invalid DB or Table name']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        
        // Fetch rows
        $stmt = $pdo->query("SELECT * FROM `$validatedTable` LIMIT 100");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Also fetch column names and structure
        $structStmt = $pdo->query("DESCRIBE `$validatedTable`");
        $structure = $structStmt->fetchAll(PDO::FETCH_ASSOC);
        $columns = array_map(function($col) {
            return [
                'name' => $col['Field'],
                'type' => $col['Type'],
                'key' => $col['Key']
            ];
        }, $structure);
        
        echo json_encode([
            'success' => true,
            'columns' => $columns,
            'rows' => $rows
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 8. Run Raw Query
if ($action === 'run_query' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $_POST['db'] ?? '';
    $query = $_POST['query'] ?? '';
    
    $validatedDb = validateIdentifier($db);
    if (!$validatedDb) {
        echo json_encode(['success' => false, 'error' => 'Invalid database context']);
        exit;
    }
    
    if (trim($query) === '') {
        echo json_encode(['success' => false, 'error' => 'Empty query']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        
        // Prepare statement and execute
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $isSelect = preg_match('/^\s*(select|show|describe|desc|explain)\b/i', $query);
        
        if ($isSelect) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $columns = [];
            if (count($rows) > 0) {
                $columns = array_keys($rows[0]);
            } else {
                // If 0 rows, try to get column names from columnCount
                for ($i = 0; $i < $stmt->columnCount(); $i++) {
                    $meta = $stmt->getColumnMeta($i);
                    if ($meta) {
                        $columns[] = $meta['name'];
                    }
                }
            }
            echo json_encode([
                'success' => true,
                'type' => 'select',
                'columns' => $columns,
                'rows' => $rows,
                'count' => count($rows)
            ]);
        } else {
            $affected = $stmt->rowCount();
            echo json_encode([
                'success' => true,
                'type' => 'dml',
                'affected_rows' => $affected,
                'message' => "Query executed successfully. $affected row(s) affected."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 9. Insert Row
if ($action === 'insert_row' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $_POST['db'] ?? '';
    $table = $_POST['table'] ?? '';
    $data = $_POST['data'] ?? ''; // JSON string of col-val pairs
    
    $validatedDb = validateIdentifier($db);
    $validatedTable = validateIdentifier($table);
    
    if (!$validatedDb || !$validatedTable) {
        echo json_encode(['success' => false, 'error' => 'Invalid DB or Table name']);
        exit;
    }
    
    $fields = json_decode($data, true);
    if (!is_array($fields) || empty($fields)) {
        echo json_encode(['success' => false, 'error' => 'No data to insert']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        
        $cols = [];
        $placeholders = [];
        $vals = [];
        
        foreach ($fields as $col => $val) {
            $validatedCol = validateIdentifier($col);
            if ($validatedCol) {
                $cols[] = "`$validatedCol`";
                $placeholders[] = "?";
                $vals[] = ($val === '' ? null : $val);
            }
        }
        
        $sql = "INSERT INTO `$validatedTable` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
        
        echo json_encode([
            'success' => true,
            'insert_id' => $pdo->lastInsertId(),
            'message' => 'Row inserted successfully!'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 10. Delete Row
if ($action === 'delete_row' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = $_POST['db'] ?? '';
    $table = $_POST['table'] ?? '';
    $pk_col = $_POST['pk_col'] ?? '';
    $pk_val = $_POST['pk_val'] ?? '';
    
    $validatedDb = validateIdentifier($db);
    $validatedTable = validateIdentifier($table);
    $validatedPkCol = validateIdentifier($pk_col);
    
    if (!$validatedDb || !$validatedTable || !$validatedPkCol) {
        echo json_encode(['success' => false, 'error' => 'Invalid identifiers']);
        exit;
    }
    
    try {
        $pdo->exec("USE `$validatedDb`");
        $stmt = $pdo->prepare("DELETE FROM `$validatedTable` WHERE `$validatedPkCol` = ?");
        $stmt->execute([$pk_val]);
        
        echo json_encode([
            'success' => true,
            'affected' => $stmt->rowCount(),
            'message' => 'Row deleted successfully!'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// 11. Get Config File
if ($action === 'get_config') {
    $file = $_GET['file'] ?? 'httpd.conf';
    if ($file !== 'httpd.conf' && $file !== 'my.ini') {
        echo json_encode(['success' => false, 'error' => 'Invalid config file name']);
        exit;
    }
    
    $filename = "mock_" . $file;
    if (!file_exists($filename)) {
        // Create mock default configs
        if ($file === 'httpd.conf') {
            $default = "# Simulated Apache HTTP Server Configuration File\n"
                     . "Listen 80\n"
                     . "Listen 443\n"
                     . "ServerName localhost:80\n"
                     . "DocumentRoot \"C:/xampp/htdocs\"\n"
                     . "<Directory \"C:/xampp/htdocs\">\n"
                     . "    Options Indexes FollowSymLinks\n"
                     . "    AllowOverride All\n"
                     . "    Require all granted\n"
                     . "</Directory>\n"
                     . "DirectoryIndex index.php index.html\n"
                     . "ErrorLog \"logs/error.log\"\n";
        } else {
            $default = "# Simulated MySQL/MariaDB Configuration File\n"
                     . "[mysqld]\n"
                     . "port=3306\n"
                     . "datadir=\"C:/xampp/mysql/data\"\n"
                     . "character-set-server=utf8mb4\n"
                     . "default-storage-engine=INNODB\n"
                     . "max_connections=150\n"
                     . "query_cache_size=8M\n"
                     . "innodb_buffer_pool_size=16M\n";
        }
        file_put_contents($filename, $default);
    }
    
    $content = file_get_contents($filename);
    echo json_encode(['success' => true, 'content' => $content]);
    exit;
}

// 12. Save Config File
if ($action === 'save_config' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_POST['file'] ?? '';
    $content = $_POST['content'] ?? '';
    
    if ($file !== 'httpd.conf' && $file !== 'my.ini') {
        echo json_encode(['success' => false, 'error' => 'Invalid config file name']);
        exit;
    }
    
    $filename = "mock_" . $file;
    if (file_put_contents($filename, $content) !== false) {
        // Log status change
        $logMsg = "Configuration file $file updated and saved.";
        $stmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");
        $stmt->execute([$logMsg]);
        
        echo json_encode(['success' => true, 'message' => "Config saved successfully!"]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to write config file']);
    }
    exit;
}

// 13. Table Indexes
if ($action === 'table_indexes') {
    $db    = $_GET['db']    ?? '';
    $table = $_GET['table'] ?? '';
    $vDb    = validateIdentifier($db);
    $vTable = validateIdentifier($table);
    if (!$vDb || !$vTable) { echo json_encode(['success'=>false,'error'=>'Invalid identifiers']); exit; }
    try {
        $pdo->exec("USE `$vDb`");
        $stmt = $pdo->query("SHOW INDEX FROM `$vTable`");
        $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true, 'indexes'=>$indexes]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
    }
    exit;
}

// 14. Table Statistics
if ($action === 'table_stats') {
    $db    = $_GET['db']    ?? '';
    $table = $_GET['table'] ?? '';
    $vDb    = validateIdentifier($db);
    $vTable = validateIdentifier($table);
    if (!$vDb || !$vTable) { echo json_encode(['success'=>false,'error'=>'Invalid identifiers']); exit; }
    try {
        $pdo->exec("USE `$vDb`");
        $stmt = $pdo->prepare("SELECT TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH, AUTO_INCREMENT, CREATE_TIME, UPDATE_TIME, ENGINE, TABLE_COLLATION
            FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $stmt->execute([$vDb, $vTable]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        // Also get row count (accurate)
        $rowStmt = $pdo->query("SELECT COUNT(*) as cnt FROM `$vTable`");
        $cnt = $rowStmt->fetchColumn();
        $stats['ACCURATE_ROWS'] = $cnt;
        echo json_encode(['success'=>true, 'stats'=>$stats]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
    }
    exit;
}

// 15. Table Operations (truncate/drop/rename/optimize/copy)
if ($action === 'table_operation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db        = $_POST['db']        ?? '';
    $table     = $_POST['table']     ?? '';
    $operation = $_POST['operation'] ?? '';
    $newName   = $_POST['new_name']  ?? '';
    $vDb    = validateIdentifier($db);
    $vTable = validateIdentifier($table);
    if (!$vDb || !$vTable) { echo json_encode(['success'=>false,'error'=>'Invalid identifiers']); exit; }
    try {
        $pdo->exec("USE `$vDb`");
        $msg = '';
        if ($operation === 'truncate') {
            $pdo->exec("TRUNCATE TABLE `$vTable`");
            $msg = "Table `$vTable` truncated successfully. All rows deleted.";
        } elseif ($operation === 'drop') {
            $pdo->exec("DROP TABLE `$vTable`");
            $msg = "Table `$vTable` dropped successfully.";
        } elseif ($operation === 'optimize') {
            $pdo->exec("OPTIMIZE TABLE `$vTable`");
            $msg = "Table `$vTable` optimized successfully.";
        } elseif ($operation === 'rename' && $newName) {
            $vNew = validateIdentifier($newName);
            if (!$vNew) { echo json_encode(['success'=>false,'error'=>'Invalid new name']); exit; }
            $pdo->exec("RENAME TABLE `$vTable` TO `$vNew`");
            $msg = "Table `$vTable` renamed to `$vNew`.";
        } elseif ($operation === 'copy' && $newName) {
            $vNew = validateIdentifier($newName);
            if (!$vNew) { echo json_encode(['success'=>false,'error'=>'Invalid copy name']); exit; }
            $pdo->exec("CREATE TABLE `$vNew` LIKE `$vTable`");
            $pdo->exec("INSERT INTO `$vNew` SELECT * FROM `$vTable`");
            $msg = "Table `$vTable` copied to `$vNew` with all data.";
        } else {
            echo json_encode(['success'=>false,'error'=>'Unknown operation or missing parameters']);
            exit;
        }
        // Log action
        $logStmt = $pdo->prepare("INSERT INTO logs (message) VALUES (?)");
        $logStmt->execute(["phpMyAdmin: $msg"]);
        echo json_encode(['success'=>true,'message'=>$msg]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

// 16. Run Multiple Statements (semicolon-separated)
if ($action === 'run_multi_query' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db      = $_POST['db']    ?? 'xampp_sim_db';
    $queries = $_POST['query'] ?? '';
    $vDb = validateIdentifier($db);
    if (!$vDb) { echo json_encode(['success'=>false,'error'=>'Invalid database']); exit; }
    if (trim($queries) === '') { echo json_encode(['success'=>false,'error'=>'Empty query']); exit; }
    try {
        $pdo->exec("USE `$vDb`");
        // Split by semicolons (simple split, skip empties and comment lines)
        $parts = array_filter(array_map('trim', explode(';', $queries)), fn($q) => $q !== '' && !preg_match('/^--/', $q));
        $results = [];
        foreach ($parts as $q) {
            try {
                $stmt = $pdo->prepare($q);
                $stmt->execute();
                $isSelect = preg_match('/^\s*(select|show|describe|desc|explain)\b/i', $q);
                if ($isSelect) {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $cols = [];
                    if (count($rows) > 0) $cols = array_keys($rows[0]);
                    else for ($i = 0; $i < $stmt->columnCount(); $i++) { $m = $stmt->getColumnMeta($i); if ($m) $cols[] = $m['name']; }
                    $results[] = ['query'=>$q,'type'=>'select','columns'=>$cols,'rows'=>$rows,'count'=>count($rows)];
                } else {
                    $results[] = ['query'=>$q,'type'=>'dml','affected_rows'=>$stmt->rowCount(),'message'=>"Query OK, ".$stmt->rowCount()." row(s) affected."];
                }
            } catch (PDOException $inner) {
                $results[] = ['query'=>$q,'type'=>'error','error'=>$inner->getMessage()];
            }
        }
        echo json_encode(['success'=>true,'results'=>$results,'total'=>count($results)]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

// 17. Server Status / SHOW STATUS
if ($action === 'server_status') {
    try {
        $stmt = $pdo->query("SHOW GLOBAL STATUS");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $status = [];
        foreach ($rows as $r) $status[$r['Variable_name']] = $r['Value'];
        // Also grab SHOW VARIABLES
        $stmt2 = $pdo->query("SHOW GLOBAL VARIABLES");
        $vars = [];
        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) $vars[$r['Variable_name']] = $r['Value'];
        echo json_encode(['success'=>true,'status'=>$status,'variables'=>$vars]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

// 18. Process List
if ($action === 'process_list') {
    try {
        $stmt = $pdo->query("SHOW FULL PROCESSLIST");
        $procs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true,'processes'=>$procs]);
    } catch (PDOException $e) {
        echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
?>

