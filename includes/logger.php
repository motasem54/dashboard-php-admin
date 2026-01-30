<?php
/**
 * Logging Functions
 */

require_once __DIR__ . '/../config/init.php';

/**
 * Log activity
 */
function logActivity($userId, $action, $description = null) {
    $conn = getConnection();
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $conn->prepare("
        INSERT INTO data_logs (user_id, action, description, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$userId, $action, $description, $ipAddress, $userAgent]);
}

/**
 * Get recent logs
 */
function getLogs($limit = 100, $offset = 0) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            dl.id,
            dl.user_id,
            u.username,
            dl.action,
            dl.description,
            dl.ip_address,
            dl.created_at
        FROM data_logs dl
        LEFT JOIN users u ON dl.user_id = u.id
        ORDER BY dl.created_at DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Get log statistics
 */
function getLogStats() {
    $conn = getConnection();
    
    $stmt = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN action = 'LOGIN_SUCCESS' THEN 1 ELSE 0 END) as successful_logins,
            SUM(CASE WHEN action = 'LOGIN_FAILED' THEN 1 ELSE 0 END) as failed_logins
        FROM data_logs
    ");
    
    return $stmt->fetch();
}
