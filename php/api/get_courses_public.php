<?php
require_once '../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'courses' => []]);
    exit();
}

// Check if courses table exists
$check = $conn->query("SHOW TABLES LIKE 'courses'");
if ($check->num_rows === 0) {
    echo json_encode(['success' => true, 'courses' => []]);
    $conn->close();
    exit();
}

$result = $conn->query("
    SELECT id, course_name, course_code, duration_years
    FROM courses
    WHERE status = 'active'
    ORDER BY course_name
");

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode(['success' => true, 'courses' => $courses]);
$conn->close();
?>
