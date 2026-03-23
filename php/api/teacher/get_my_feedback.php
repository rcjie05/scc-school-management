<?php
require_once '../../config.php';
requireRole('teacher');
header('Content-Type: application/json');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, subject, message, status, response, user_reply,
           DATE_FORMAT(created_at, '%M %d, %Y %h:%i %p') as date
    FROM feedback
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$feedback = [];
while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

echo json_encode(['success' => true, 'feedback' => $feedback]);
$conn->close();
?>
