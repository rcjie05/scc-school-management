<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('student');

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get student's section_id
$sec_stmt = $conn->prepare("SELECT section_id FROM users WHERE id=?");
$sec_stmt->bind_param('i', $user_id);
$sec_stmt->execute();
$row = $sec_stmt->get_result()->fetch_assoc();
$section_id = $row['section_id'] ?? null;

if (!$section_id) {
    echo json_encode(['success' => true, 'subjects' => [], 'message' => 'No section assigned.']);
    $conn->close();
    exit();
}

// Get all subjects in their section NOT already in their study load
$stmt = $conn->prepare("
    SELECT s.id, s.subject_code, s.subject_name, s.units,
           u.name AS teacher,
           GROUP_CONCAT(
               DISTINCT CONCAT(sch.day_of_week,' ',
                   TIME_FORMAT(sch.start_time,'%h:%i%p'),'-',
                   TIME_FORMAT(sch.end_time,'%h:%i%p'))
               SEPARATOR ', '
           ) AS schedule
    FROM section_subjects ss
    JOIN subjects s ON ss.subject_id = s.id
    LEFT JOIN users u ON ss.teacher_id = u.id
    LEFT JOIN section_schedules sch ON sch.section_subject_id = ss.id
    WHERE ss.section_id = ?
    AND s.id NOT IN (SELECT subject_id FROM study_loads WHERE student_id = ?)
    GROUP BY s.id, s.subject_code, s.subject_name, s.units, u.name
    ORDER BY s.subject_code
");
$stmt->bind_param('ii', $section_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode(['success' => true, 'subjects' => $subjects]);
$conn->close();
?>
