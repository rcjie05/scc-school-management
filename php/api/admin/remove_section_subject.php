<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$section_subject_id = (int)($data['section_subject_id'] ?? 0);

if (!$section_subject_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM section_subjects WHERE id = ?");
$stmt->bind_param('i', $section_subject_id);

if ($stmt->execute()) {
    logAction($conn, $_SESSION['user_id'], "Removed subject from section (section_subject_id=$section_subject_id)", 'section_subjects', $section_subject_id);
    echo json_encode(['success' => true, 'message' => 'Subject removed from section']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove subject']);
}

$stmt->close();
$conn->close();
?>
