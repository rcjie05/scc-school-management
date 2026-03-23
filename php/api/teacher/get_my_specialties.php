<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('teacher');

$conn = getDBConnection();
$teacher_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        ts.id,
        ts.proficiency_level,
        ts.is_primary,
        ts.assigned_date,
        s.id as subject_id,
        s.subject_code,
        s.subject_name,
        s.description,
        s.units,
        s.course,
        s.year_level,
        s.prerequisites
    FROM teacher_specialties ts
    INNER JOIN subjects s ON ts.subject_id = s.id
    WHERE ts.teacher_id = ? AND s.status = 'active'
    ORDER BY ts.is_primary DESC, s.subject_code ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
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
