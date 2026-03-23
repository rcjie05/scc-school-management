<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$input = json_decode(file_get_contents('php://input'), true);
$student_id = intval($input['student_id'] ?? 0);
$subject_id = intval($input['subject_id'] ?? 0);

if (!$student_id || !$subject_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$conn = getDBConnection();

// Check if already added
$stmt = $conn->prepare("SELECT id FROM study_loads WHERE student_id = ? AND subject_id = ?");
$stmt->bind_param("ii", $student_id, $subject_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Subject already added']);
    exit();
}

// Add subject
$stmt = $conn->prepare("INSERT INTO study_loads (student_id, subject_id, status) VALUES (?, ?, 'draft')");
$stmt->bind_param("ii", $student_id, $subject_id);

if ($stmt->execute()) {
    logAction($conn, $_SESSION['user_id'], "Added subject to student load", 'study_loads', $conn->insert_id);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add subject']);
}

$conn->close();
?>
