<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, subject_code, subject_name, units, course, year_level FROM subjects ORDER BY subject_code");
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) $subjects[] = $row;

echo json_encode(['success' => true, 'subjects' => $subjects]);
$conn->close();
?>
