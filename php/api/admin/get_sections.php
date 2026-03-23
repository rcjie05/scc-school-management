<?php
require_once '../../config.php';
requireRole('admin');

header('Content-Type: application/json');

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$course     = $_GET['course']     ?? '';
$year_level = $_GET['year_level'] ?? '';
$semester   = $_GET['semester']   ?? '';
$status     = $_GET['status']     ?? '';
$search     = $_GET['search']     ?? '';

$where = ['1=1'];
$params = [];
$types  = '';

if ($course)     { $where[] = 's.course = ?';     $params[] = $course;     $types .= 's'; }
if ($year_level) { $where[] = 's.year_level = ?'; $params[] = $year_level; $types .= 's'; }
if ($semester)   { $where[] = 's.semester = ?';   $params[] = $semester;   $types .= 's'; }
if ($status)     { $where[] = 's.status = ?';     $params[] = $status;     $types .= 's'; }
if ($search) {
    $like = "%$search%";
    $where[] = '(s.section_name LIKE ? OR s.section_code LIKE ?)';
    $params[] = $like; $params[] = $like;
    $types .= 'ss';
}

$whereStr = implode(' AND ', $where);
$sql = "SELECT s.*,
    u.name AS adviser_name,
    (SELECT COUNT(*) FROM section_subjects ss WHERE ss.section_id = s.id) AS subject_count,
    (SELECT COUNT(*) FROM section_schedules sch WHERE sch.section_id = s.id) AS schedule_count
    FROM sections s
    LEFT JOIN users u ON s.adviser_id = u.id
    WHERE $whereStr
    ORDER BY s.school_year DESC, s.year_level, s.section_name";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

echo json_encode(['success' => true, 'sections' => $sections]);
$conn->close();
?>
