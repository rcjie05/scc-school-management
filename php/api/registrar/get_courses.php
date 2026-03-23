<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$conn = getDBConnection();

$sql = "SELECT id, course_name, course_code FROM courses WHERE status = 'active' ORDER BY course_name ASC";
$result = $conn->query($sql);

$courses = [];
while ($row = $result->fetch_assoc()) $courses[] = $row;

echo json_encode(['success' => true, 'courses' => $courses]);
$conn->close();
?>
