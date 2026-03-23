<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$subject_id = isset($input['subject_id']) ? intval($input['subject_id']) : null;

if (!$subject_id) {
    echo json_encode(['success' => false, 'message' => 'Subject ID is required']);
    exit();
}

// Get subject info before deletion for logging
$stmt = $conn->prepare("SELECT subject_code, subject_name FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Subject not found']);
    exit();
}

$subject = $result->fetch_assoc();

// Delete the subject (cascades to teacher_specialties due to foreign key)
$stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
$stmt->bind_param("i", $subject_id);

if ($stmt->execute()) {
    logAction($conn, $admin_id, "Deleted subject: {$subject['subject_code']} - {$subject['subject_name']}", 'subjects', $subject_id);
    echo json_encode(['success' => true, 'message' => 'Subject deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete subject: ' . $stmt->error]);
}

$conn->close();
?>
