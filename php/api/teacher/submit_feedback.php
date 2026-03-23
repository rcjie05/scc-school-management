<?php
require_once '../../config.php';
requireRole('teacher');

header('Content-Type: application/json');

$conn = getDBConnection();
$input = json_decode(file_get_contents('php://input'), true);

$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'Subject and message are required.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
$stmt->bind_param("iss", $user_id, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit feedback.']);
}

$conn->close();
?>
