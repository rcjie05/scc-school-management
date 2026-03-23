<?php
require_once '../../config.php';
requireRole('registrar');
header('Content-Type: application/json');

$conn = getDBConnection();
$input = json_decode(file_get_contents('php://input'), true);

$feedback_id = $input['feedback_id'] ?? null;
$response    = $input['response']    ?? null;
$status      = $input['status']      ?? 'in_progress';

if (!$feedback_id) {
    echo json_encode(['success' => false, 'message' => 'Feedback ID is required.']);
    exit;
}

if ($response) {
    $stmt = $conn->prepare("UPDATE feedback SET response = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $response, $status, $feedback_id);
} else {
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $feedback_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Feedback updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update feedback.']);
}

$conn->close();
?>
