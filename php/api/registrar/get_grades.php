<?php
require_once '../../config.php';
requireRole('registrar');

header('Content-Type: application/json');

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    $semester    = $conn->real_escape_string($_GET['semester']    ?? '');
    $school_year = $conn->real_escape_string($_GET['school_year'] ?? '');

    $sql = "SELECT
                g.id,
                u.student_id   AS student_number,
                u.name         AS student_name,
                u.course,
                u.year_level,
                sub.subject_code,
                sub.subject_name,
                sub.units,
                g.midterm_grade,
                g.final_grade,
                g.remarks,
                g.semester,
                g.school_year
            FROM grades g
            JOIN users u    ON u.id  = g.student_id AND u.role = 'student'
            JOIN subjects sub ON sub.id = g.subject_id
            WHERE 1=1";

    if ($semester)    $sql .= " AND g.semester    = '$semester'";
    if ($school_year) $sql .= " AND g.school_year = '$school_year'";

    $sql .= " ORDER BY u.student_id, sub.subject_code";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }

    $conn->close();
    echo json_encode(['success' => true, 'grades' => $grades]);

} catch (Exception $e) {
    if (isset($conn)) $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching grades: ' . $e->getMessage()]);
}
?>
