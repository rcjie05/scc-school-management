<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];
$input    = json_decode(file_get_contents('php://input'), true);
$room_id  = !empty($input['room_id']) ? intval($input['room_id']) : null;

if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit();
}

// Get room name for audit log
$name_stmt = $conn->prepare("SELECT room_number FROM rooms WHERE id=?");
$name_stmt->bind_param("i", $room_id);
$name_stmt->execute();
$row = $name_stmt->get_result()->fetch_assoc();
$name_stmt->close();
if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Room not found']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM rooms WHERE id=?");
$stmt->bind_param("i", $room_id);
if ($stmt->execute()) {
    logAction($conn, $admin_id, "Deleted room: " . $row['room_number'], 'rooms', $room_id);
    echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete: ' . $stmt->error]);
}

$conn->close();
?>
