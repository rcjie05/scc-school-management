<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Auto-add deleted_at column if missing
$conn->query("ALTER TABLE announcements ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");

$input = json_decode(file_get_contents('php://input'), true);
$id    = intval($input['announcement_id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID required']);
    exit();
}

$stmt = $conn->prepare("SELECT title, deleted_at FROM announcements WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Announcement not found']);
    exit();
}
if ($row['deleted_at']) {
    echo json_encode(['success' => false, 'message' => 'Announcement is already in the recycle bin']);
    exit();
}

$stmt = $conn->prepare("UPDATE announcements SET deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    logAction($conn, $admin_id, "Moved announcement to recycle bin: {$row['title']}", 'announcements', $id);
    echo json_encode(['success' => true, 'message' => "'{$row['title']}' moved to recycle bin"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete announcement']);
}
$conn->close();
