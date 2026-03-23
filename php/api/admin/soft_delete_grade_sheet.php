<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Auto-add deleted_at column if missing
$conn->query("ALTER TABLE grade_submissions ADD COLUMN IF NOT EXISTS deleted_at DATETIME DEFAULT NULL");

$input = json_decode(file_get_contents('php://input'), true);
$id    = intval($input['submission_id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Submission ID required']);
    exit();
}

$stmt = $conn->prepare("SELECT gs.id, gs.file_name, u.name AS teacher_name, gs.deleted_at 
                        FROM grade_submissions gs 
                        LEFT JOIN users u ON u.id = gs.teacher_id
                        WHERE gs.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Grade sheet not found']);
    exit();
}
if ($row['deleted_at']) {
    echo json_encode(['success' => false, 'message' => 'Already in the recycle bin']);
    exit();
}

$stmt = $conn->prepare("UPDATE grade_submissions SET deleted_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    logAction($conn, $admin_id, "Moved grade sheet to recycle bin: {$row['file_name']} by {$row['teacher_name']}", 'grade_submissions', $id);
    echo json_encode(['success' => true, 'message' => "Grade sheet moved to recycle bin"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete grade sheet']);
}
$conn->close();
