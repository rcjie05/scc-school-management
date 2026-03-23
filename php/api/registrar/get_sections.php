<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$conn = getDBConnection();

// Return empty if sections table doesn't exist yet
if ($conn->query("SHOW TABLES LIKE 'sections'")->num_rows === 0) {
    echo json_encode(['success' => true, 'sections' => []]);
    $conn->close();
    exit();
}

$status = $_GET['status'] ?? '';
$sql = "
    SELECT s.*, u.name AS adviser_name,
    (SELECT COUNT(*) FROM section_subjects ss WHERE ss.section_id = s.id) AS subject_count
    FROM sections s
    LEFT JOIN users u ON s.adviser_id = u.id
    " . ($status ? "WHERE s.status = '" . $conn->real_escape_string($status) . "'" : "") . "
    ORDER BY s.school_year DESC, s.year_level, s.section_name
";

$result = $conn->query($sql);
$sections = [];
while ($row = $result->fetch_assoc()) $sections[] = $row;

echo json_encode(['success' => true, 'sections' => $sections]);
$conn->close();
?>
