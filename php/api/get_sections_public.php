<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$course     = $_GET['course']     ?? '';
$year_level = $_GET['year_level'] ?? '';
$semester   = $_GET['semester']   ?? '';

$where  = ["s.status = 'active'"];
$params = [];
$types  = '';

if ($course) {
    $where[]  = 's.course = ?';
    $params[] = $course;
    $types   .= 's';
}
if ($year_level) {
    $where[]  = 's.year_level = ?';
    $params[] = $year_level;
    $types   .= 's';
}
if ($semester) {
    $where[]  = 's.semester = ?';
    $params[] = $semester;
    $types   .= 's';
}

$whereStr = implode(' AND ', $where);
$sql = "SELECT id, section_name, section_code, course, year_level, semester, school_year, max_students
        FROM sections s
        WHERE $whereStr
        ORDER BY s.year_level, s.section_name";

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
