<?php
// Debug script to test user creation and update functionality
// Place this in /php/api/admin/test_debug.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config.php';
header('Content-Type: application/json');

// Bypass role check for debugging
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not logged in', 'session' => $_SESSION]);
    exit();
}

echo json_encode([
    'message' => 'Debug test',
    'logged_in' => isLoggedIn(),
    'user_id' => $_SESSION['user_id'] ?? 'not set',
    'role' => $_SESSION['role'] ?? 'not set',
    'has_admin_role' => hasRole('admin'),
    'php_version' => phpversion(),
    'post_data' => file_get_contents('php://input'),
    'connection_test' => testConnection()
]);

function testConnection() {
    $conn = getDBConnection();
    if (!$conn) {
        return 'Failed to connect';
    }
    
    // Test if we can query users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    return 'Connection OK - ' . $row['count'] . ' users in database';
}
?>
