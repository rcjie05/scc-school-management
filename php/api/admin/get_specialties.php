<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();

$teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : null;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;

$sql = "
    SELECT 
        ts.id,
        ts.teacher_id,
        ts.subject_id,
        ts.proficiency_level,
        ts.is_primary,
        ts.assigned_date,
        u.name as teacher_name,
        s.subject_code,
        s.subject_name
    FROM teacher_specialties ts
    INNER JOIN users u ON ts.teacher_id = u.id
    INNER JOIN subjects s ON ts.subject_id = s.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($teacher_id) {
    $sql .= " AND ts.teacher_id = ?";
    $params[] = $teacher_id;
    $types .= "i";
}

if ($subject_id) {
    $sql .= " AND ts.subject_id = ?";
    $params[] = $subject_id;
    $types .= "i";
}

$sql .= " ORDER BY u.name ASC, s.subject_code ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$specialties = [];
while ($row = $result->fetch_assoc()) {
    $specialties[] = $row;
}

echo json_encode([
    'success' => true,
    'specialties' => $specialties
]);

$conn->close();
?>
