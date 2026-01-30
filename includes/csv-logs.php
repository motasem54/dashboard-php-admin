<?php
/**
 * CSV Logs Functions
 */

require_once __DIR__ . '/../config/init.php';

/**
 * Create CSV logs table
 */
function createCsvLogsTable() {
    $conn = getConnection();
    
    $conn->exec("
        CREATE TABLE IF NOT EXISTS csv_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            timestamp DATETIME NOT NULL,
            source VARCHAR(50),
            message TEXT,
            username VARCHAR(100),
            action_type VARCHAR(50),
            ip_address VARCHAR(45),
            mac_address VARCHAR(17),
            imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            imported_by INT,
            INDEX idx_timestamp (timestamp),
            INDEX idx_username (username),
            INDEX idx_action_type (action_type),
            FOREIGN KEY (imported_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

/**
 * Parse CSV file and import to database
 */
function importCsvFile($filePath, $userId) {
    $conn = getConnection();
    $imported = 0;
    $errors = [];
    
    if (!file_exists($filePath)) {
        return ['success' => false, 'error' => 'الملف غير موجود'];
    }
    
    $file = fopen($filePath, 'r');
    if (!$file) {
        return ['success' => false, 'error' => 'خطأ في فتح الملف'];
    }
    
    // Skip header row
    $header = fgetcsv($file);
    
    $stmt = $conn->prepare("
        INSERT INTO csv_logs (timestamp, source, message, username, action_type, ip_address, mac_address, imported_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    while (($row = fgetcsv($file)) !== false) {
        if (count($row) < 3) continue;
        
        $timestamp = $row[0] ?? null;
        $source = $row[1] ?? null;
        $message = $row[2] ?? null;
        
        // Parse timestamp
        if ($timestamp) {
            $timestamp = date('Y-m-d H:i:s', strtotime($timestamp));
        }
        
        // Extract username from message
        $username = null;
        if (preg_match('/user ([\w\.]+)/', $message, $matches)) {
            $username = $matches[1];
        } elseif (preg_match('/account ([\w\.]+)/', $message, $matches)) {
            $username = $matches[1];
        } elseif (preg_match('/<pppoe-([\w\.]+)>/', $message, $matches)) {
            $username = $matches[1];
        }
        
        // Extract action type
        $actionType = null;
        if (strpos($message, 'authenticated') !== false) {
            $actionType = 'authenticated';
        } elseif (strpos($message, 'logged in') !== false) {
            $actionType = 'login';
        } elseif (strpos($message, 'logged out') !== false) {
            $actionType = 'logout';
        } elseif (strpos($message, 'authentication failed') !== false) {
            $actionType = 'auth_failed';
        } elseif (strpos($message, 'connected') !== false) {
            $actionType = 'connected';
        } elseif (strpos($message, 'disconnected') !== false) {
            $actionType = 'disconnected';
        }
        
        // Extract IP address
        $ipAddress = $source ?? null;
        
        // Extract MAC address
        $macAddress = null;
        if (preg_match('/([0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2}:[0-9A-F]{2})/i', $message, $matches)) {
            $macAddress = $matches[1];
        }
        
        try {
            $stmt->execute([
                $timestamp,
                $source,
                $message,
                $username,
                $actionType,
                $ipAddress,
                $macAddress,
                $userId
            ]);
            $imported++;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    fclose($file);
    
    return [
        'success' => true,
        'imported' => $imported,
        'errors' => $errors
    ];
}

/**
 * Get CSV logs with pagination and filtering
 */
function getCsvLogs($limit = 100, $offset = 0, $filters = []) {
    $conn = getConnection();
    
    $where = [];
    $params = [];
    
    if (!empty($filters['username'])) {
        $where[] = 'username LIKE ?';
        $params[] = '%' . $filters['username'] . '%';
    }
    
    if (!empty($filters['action_type'])) {
        $where[] = 'action_type = ?';
        $params[] = $filters['action_type'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = 'timestamp >= ?';
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = 'timestamp <= ?';
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $sql = "SELECT * FROM csv_logs {$whereClause} ORDER BY timestamp DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Get CSV logs count
 */
function getCsvLogsCount($filters = []) {
    $conn = getConnection();
    
    $where = [];
    $params = [];
    
    if (!empty($filters['username'])) {
        $where[] = 'username LIKE ?';
        $params[] = '%' . $filters['username'] . '%';
    }
    
    if (!empty($filters['action_type'])) {
        $where[] = 'action_type = ?';
        $params[] = $filters['action_type'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = 'timestamp >= ?';
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = 'timestamp <= ?';
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    $stmt = $conn->query("SELECT COUNT(*) FROM csv_logs {$whereClause}");
    return $stmt->fetchColumn();
}

/**
 * Get CSV logs statistics
 */
function getCsvLogsStats() {
    $conn = getConnection();
    
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            COUNT(DISTINCT username) as unique_users,
            SUM(CASE WHEN action_type = 'login' THEN 1 ELSE 0 END) as total_logins,
            SUM(CASE WHEN action_type = 'logout' THEN 1 ELSE 0 END) as total_logouts,
            SUM(CASE WHEN action_type = 'auth_failed' THEN 1 ELSE 0 END) as failed_auths
        FROM csv_logs
    ");
    
    return $stmt->fetch();
}

// Create table on load
try {
    createCsvLogsTable();
} catch (Exception $e) {
    // Table might already exist
}
