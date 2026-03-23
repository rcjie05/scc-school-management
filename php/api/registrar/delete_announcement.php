<?php
require_once '../../config.php';
requireRole('registrar');

header('Content-Type: application/json');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$announcement_id = isset($input['announcement_id']) ? intval($input['announcement_id']) : null;

if (!$announcement_id) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND posted_by = ?");
$stmt->bind_param("ii", $announcement_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Announcement deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Announcement not found or you do not have permission to delete it']);
}

$conn->close();
?>
