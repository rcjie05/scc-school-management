<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT id, name, email, student_id, course, year_level, status, created_at
    FROM users
    WHERE role = 'student'
    ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

echo json_encode(['success' => true, 'applications' => $applications]);
$conn->close();
?>
