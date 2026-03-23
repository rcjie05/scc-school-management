<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('student');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT s.subject_code, s.subject_name, s.units,
           g.midterm_grade, g.final_grade, g.remarks
    FROM grades g
    JOIN subjects s ON g.subject_id = s.id
    WHERE g.student_id = ?
    ORDER BY s.subject_code
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
$totalGrade = 0;
$gradeCount = 0;
$passed = 0;
$units = 0;

while ($row = $result->fetch_assoc()) {
    $grades[] = [
        'subject_code' => $row['subject_code'],
        'subject_name' => $row['subject_name'],
        'units' => $row['units'],
        'midterm' => $row['midterm_grade'],
        'final' => $row['final_grade'],
        'remarks' => $row['remarks']
    ];
    
    if ($row['final_grade'] !== null && $row['final_grade'] !== '') {
        $totalGrade += $row['final_grade'];
        $gradeCount++;
        // Support Philippine scale (1.0-5.0, pass = <=3.0) and percentage scale (>5, pass = >=75)
        $isPassed = ($row['final_grade'] <= 5.0) ? ($row['final_grade'] <= 3.0) : ($row['final_grade'] >= 75);
        if ($isPassed) {
            $passed++;
            $units += $row['units'];
        }
    }
}

$gpa = $gradeCount > 0 ? number_format($totalGrade / $gradeCount, 2) : null;

echo json_encode([
    'success' => true,
    'stats' => [
        'gpa' => $gpa,
        'passed' => $passed,
        'units' => $units
    ],
    'grades' => $grades
]);

$conn->close();
?>
