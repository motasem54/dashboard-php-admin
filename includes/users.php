<?php
/**
 * User Management Functions
 */

require_once __DIR__ . '/../config/init.php';

/**
 * Get all users
 */
function getAllUsers() {
    $conn = getConnection();
    
    $stmt = $conn->query("
        SELECT id, username, email, role, created_at, updated_at
        FROM users
        ORDER BY created_at DESC
    ");
    
    return $stmt->fetchAll();
}

/**
 * Get user by ID
 */
function getUserById($id) {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    return $stmt->fetch();
}

/**
 * Get user count
 */
function getUserCount() {
    $conn = getConnection();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn();
}

/**
 * Create new user
 */
function createUser($username, $email, $password, $role = 'user') {
    $conn = getConnection();
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role)
        VALUES (?, ?, ?, ?)
    ");
    
    try {
        $stmt->execute([$username, $email, $hashedPassword, $role]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}
