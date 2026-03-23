<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$schedule_id = (int)($data['schedule_id'] ?? 0);

if (!$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM section_schedules WHERE id = ?");
$stmt->bind_param('i', $schedule_id);

if ($stmt->execute()) {
    logAction($conn, $_SESSION['user_id'], "Deleted schedule slot (id=$schedule_id)", 'section_schedules', $schedule_id);
    echo json_encode(['success' => true, 'message' => 'Schedule slot removed']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove schedule']);
}

$stmt->close();
$conn->close();
?>
