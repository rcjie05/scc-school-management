<?php
require_once '../../config.php';
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

requireRole('admin');

$conn = getDBConnection();

if (!$conn) {
    error_log("get_archived_users.php: Database connection failed");
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Ensure archived_at column exists (safe migration)
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS archived_at DATETIME DEFAULT NULL");

$role   = isset($_GET['role'])   ? $_GET['role']   : null;
$search = isset($_GET['search']) ? $_GET['search'] : null;

$sql = "SELECT id, name, email, role, status, student_id, course, year_level, department,
        office_location, office_hours,
        DATE_FORMAT(created_at, '%M %d, %Y') as created_date,
        DATE_FORMAT(archived_at, '%M %d, %Y %h:%i %p') as archived_date
        FROM users WHERE archived_at IS NOT NULL";

$params = [];
$types  = "";

if ($role) {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types   .= "s";
}
if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $term     = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types   .= "sss";
}

$sql .= " ORDER BY archived_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("get_archived_users.php: Failed to prepare statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
    $conn->close();
    exit();
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    error_log("get_archived_users.php: Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Query execution failed: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}

$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'users'   => $users,
    'total'   => count($users)
]);
