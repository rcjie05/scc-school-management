<?php
require_once '../../config.php';
requireRole('student');
header('Content-Type: application/json');

$conn = getDBConnection();
$input = json_decode(file_get_contents('php://input'), true);

$feedback_id = intval($input['feedback_id'] ?? 0);
$user_reply  = trim($input['user_reply'] ?? '');
$user_id     = $_SESSION['user_id'];

if (!$feedback_id || !$user_reply) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID and reply are required.']);
    exit;
}

// Make sure this feedback belongs to this student
$check = $conn->prepare("SELECT id, status FROM feedback WHERE id = ? AND user_id = ?");
$check->bind_param("ii", $feedback_id, $user_id);
$check->execute();
$row = $check->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Feedback not found.']);
    exit;
}

$stmt = $conn->prepare("UPDATE feedback SET user_reply = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sii", $user_reply, $feedback_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reply sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send reply.']);
}

$conn->close();
?>
