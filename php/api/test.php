<?php
/**
 * API Test File - Place this in /php/api/test.php
 * Access it at: http://localhost/school-management-system/php/api/test.php
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test 1: Basic response
echo json_encode([
    'test' => 'API is accessible',
    'timestamp' => date('Y-m-d H:i:s'),
    'script_path' => __FILE__,
    'current_dir' => __DIR__
]);

// Test 2: Check if config.php can be loaded
try {
    require_once __DIR__ . '/../config.php';
    echo "\n\nConfig loaded successfully!";
} catch (Exception $e) {
    echo "\n\nConfig load failed: " . $e->getMessage();
}

// Test 3: Check session
session_start();
echo "\n\nSession status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive');
echo "\nSession ID: " . session_id();
echo "\nLogged in: " . (isLoggedIn() ? 'Yes' : 'No');
if (isset($_SESSION['user_id'])) {
    echo "\nUser ID: " . $_SESSION['user_id'];
    echo "\nRole: " . $_SESSION['role'];
}

// Test 4: Check database connection
$conn = getDBConnection();
if ($conn) {
    echo "\n\nDatabase connected successfully!";
    $conn->close();
} else {
    echo "\n\nDatabase connection failed!";
}
?>
