<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$section_id = (int)($data['section_id'] ?? 0);
$subject_id = (int)($data['subject_id'] ?? 0);
$teacher_id = !empty($data['teacher_id']) ? (int)$data['teacher_id'] : null;

if (!$section_id || !$subject_id) {
    echo json_encode(['success' => false, 'message' => 'Section and subject are required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if already exists
$check = $conn->prepare("SELECT id FROM section_subjects WHERE section_id = ? AND subject_id = ?");
$check->bind_param('ii', $section_id, $subject_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This subject is already assigned to this section']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?)");
$stmt->bind_param('iii', $section_id, $subject_id, $teacher_id);

if ($stmt->execute()) {
    $new_id = $conn->insert_id;
    logAction($conn, $_SESSION['user_id'], "Added subject to section (section_id=$section_id, subject_id=$subject_id)", 'section_subjects', $new_id);
    echo json_encode(['success' => true, 'message' => 'Subject assigned to section', 'id' => $new_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign subject: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
