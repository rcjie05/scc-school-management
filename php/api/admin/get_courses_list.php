<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$result = $conn->query("SELECT id, course_name, course_code FROM courses WHERE status='active' ORDER BY course_name");
$courses = [];
while ($row = $result->fetch_assoc()) $courses[] = $row;
echo json_encode(['success' => true, 'courses' => $courses]);
$conn->close();
?>
