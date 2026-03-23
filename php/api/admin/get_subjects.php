<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();

$course     = isset($_GET['course'])     ? sanitizeInput($_GET['course'])     : '';
$year_level = isset($_GET['year_level']) ? sanitizeInput($_GET['year_level']) : '';
$status     = isset($_GET['status'])     ? sanitizeInput($_GET['status'])     : '';
$search     = isset($_GET['search'])     ? sanitizeInput($_GET['search'])     : '';

// Special sentinel: course=__none__ means only subjects with no course (All-Courses/shared subjects)
$course_none = ($course === '__none__');

$sql    = "SELECT * FROM subjects WHERE 1=1";
$params = [];
$types  = "";

if ($course_none) {
    // subjects where course IS NULL or empty string
    $sql .= " AND (course IS NULL OR course = '')";
} elseif (!empty($course)) {
    $sql .= " AND course = ?";
    $params[] = $course;
    $types   .= "s";
}

if (!empty($year_level)) {
    $sql .= " AND year_level = ?";
    $params[] = $year_level;
    $types   .= "s";
}

if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types   .= "s";
}

if (!empty($search)) {
    $sql .= " AND (subject_code LIKE ? OR subject_name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types   .= "ss";
}

$sql .= " ORDER BY subject_code ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode([
    'success'  => true,
    'subjects' => $subjects
]);

$conn->close();
?>
