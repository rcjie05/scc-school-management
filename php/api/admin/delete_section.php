<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$section_id = (int)($data['section_id'] ?? 0);

if (!$section_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid section ID']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get section info for logging
$stmt = $conn->prepare("SELECT section_code FROM sections WHERE id = ?");
$stmt->bind_param('i', $section_id);
$stmt->execute();
$result = $stmt->get_result();
$section = $result->fetch_assoc();

if (!$section) {
    echo json_encode(['success' => false, 'message' => 'Section not found']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
$stmt->bind_param('i', $section_id);

if ($stmt->execute()) {
    logAction($conn, $_SESSION['user_id'], "Deleted section: {$section['section_code']}", 'sections', $section_id);
    echo json_encode(['success' => true, 'message' => 'Section deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete section']);
}

$stmt->close();
$conn->close();
?>
