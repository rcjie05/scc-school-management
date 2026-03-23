<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Ensure archived_at column exists
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS archived_at DATETIME DEFAULT NULL");

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

$user_id = intval($input['user_id']);

$stmt = $conn->prepare("SELECT name, email, role, archived_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

if (!$user['archived_at']) {
    echo json_encode(['success' => false, 'message' => 'User is not archived']);
    exit();
}

$stmt = $conn->prepare("UPDATE users SET archived_at = NULL, status = 'active' WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    logAction($conn, $admin_id, "Restored archived user: {$user['name']} ({$user['email']}, {$user['role']})", 'users', $user_id);
    echo json_encode(['success' => true, 'message' => "User '{$user['name']}' has been restored"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore user: ' . $stmt->error]);
}

$conn->close();
