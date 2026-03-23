<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : null;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;
$semester = isset($_GET['semester']) ? $_GET['semester'] : null;
$school_year = isset($_GET['school_year']) ? $_GET['school_year'] : null;

$sql = "
    SELECT 
        g.id,
        g.student_id,
        u.name as student_name,
        u.student_id as student_number,
        u.course,
        u.year_level,
        s.subject_code,
        s.subject_name,
        s.units,
        g.midterm_grade,
        g.final_grade,
        g.semester,
        g.school_year,
        g.remarks,
        DATE_FORMAT(g.created_at, '%M %d, %Y') as date_recorded
    FROM grades g
    JOIN users u ON g.student_id = u.id
    JOIN subjects s ON g.subject_id = s.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($student_id) {
    $sql .= " AND g.student_id = ?";
    $params[] = $student_id;
    $types .= "i";
}

if ($subject_id) {
    $sql .= " AND g.subject_id = ?";
    $params[] = $subject_id;
    $types .= "i";
}

if ($semester) {
    $sql .= " AND g.semester = ?";
    $params[] = $semester;
    $types .= "s";
}

if ($school_year) {
    $sql .= " AND g.school_year = ?";
    $params[] = $school_year;
    $types .= "s";
}

$sql .= " ORDER BY g.school_year DESC, g.semester DESC, u.name, s.subject_code";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

echo json_encode([
    'success' => true,
    'grades' => $grades,
    'total' => count($grades)
]);

$conn->close();
?>
