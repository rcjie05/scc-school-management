<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('student');

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT adr.id, adr.subject_id, adr.request_type, adr.reason, adr.status, adr.registrar_note,
           adr.created_at, adr.reviewed_at,
           s.subject_code, s.subject_name, s.units,
           u.name AS reviewed_by_name
    FROM add_drop_requests adr
    JOIN subjects s ON adr.subject_id = s.id
    LEFT JOIN users u ON adr.reviewed_by = u.id
    WHERE adr.student_id = ?
    ORDER BY adr.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $row['created_at']  = date('M d, Y h:i A', strtotime($row['created_at']));
    $row['reviewed_at'] = $row['reviewed_at'] ? date('M d, Y h:i A', strtotime($row['reviewed_at'])) : null;
    $requests[] = $row;
}

echo json_encode(['success' => true, 'requests' => $requests]);
$conn->close();
?>
