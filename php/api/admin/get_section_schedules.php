<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$section_id = (int)($_GET['section_id'] ?? 0);

if (!$section_id) {
    echo json_encode(['success' => false, 'message' => 'Section ID required']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("
    SELECT sch.*,
           ss.section_id,
           sub.subject_code, sub.subject_name, sub.subject_type,
           u.name AS teacher_name,
           TIME_FORMAT(sch.start_time, '%h:%i %p') AS start_time_fmt,
           TIME_FORMAT(sch.end_time,   '%h:%i %p') AS end_time_fmt,
           TIMESTAMPDIFF(MINUTE, CONCAT('2000-01-01 ', sch.start_time), CONCAT('2000-01-01 ', sch.end_time)) AS duration_minutes
    FROM section_schedules sch
    JOIN section_subjects ss ON sch.section_subject_id = ss.id
    JOIN subjects sub ON ss.subject_id = sub.id
    LEFT JOIN users u ON ss.teacher_id = u.id
    WHERE sch.section_id = ?
    ORDER BY FIELD(sch.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), sch.start_time
");
$stmt->bind_param('i', $section_id);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}

echo json_encode(['success' => true, 'schedules' => $schedules]);
$stmt->close();
$conn->close();
?>
