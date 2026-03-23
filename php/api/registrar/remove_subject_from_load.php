<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$input = json_decode(file_get_contents('php://input'), true);
$student_id = intval($input['student_id'] ?? 0);
$subject_id = intval($input['subject_id'] ?? 0);

$conn = getDBConnection();
$stmt = $conn->prepare("DELETE FROM study_loads WHERE student_id = ? AND subject_id = ?");
$stmt->bind_param("ii", $student_id, $subject_id);

if ($stmt->execute()) {
    logAction($conn, $_SESSION['user_id'], "Removed subject from student load", 'study_loads', 0);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$conn->close();
?>
