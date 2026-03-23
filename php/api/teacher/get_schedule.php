<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('teacher');

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get all schedules from sections where this teacher is assigned to a subject
$stmt = $conn->prepare("
    SELECT 
        sec.id AS section_id,
        sec.section_name,
        sec.section_code,
        sec.course,
        sec.year_level,
        sec.semester,
        sec.school_year,
        sub.subject_code,
        sub.subject_name,
        sub.units,
        sch.day_of_week,
        sch.room,
        TIME_FORMAT(sch.start_time, '%h:%i %p') AS start_time_fmt,
        TIME_FORMAT(sch.end_time,   '%h:%i %p') AS end_time_fmt,
        (SELECT COUNT(*) FROM users u2 WHERE u2.section_id = sec.id AND u2.role = 'student') AS student_count
    FROM section_subjects ss
    JOIN sections sec ON ss.section_id = sec.id
    JOIN subjects sub ON ss.subject_id = sub.id
    JOIN section_schedules sch ON sch.section_subject_id = ss.id
    WHERE ss.teacher_id = ?
    ORDER BY 
        FIELD(sch.day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
        sch.start_time
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$schedule_by_day = [
    'Monday'    => [],
    'Tuesday'   => [],
    'Wednesday' => [],
    'Thursday'  => [],
    'Friday'    => [],
    'Saturday'  => [],
    'Sunday'    => []
];

while ($row = $result->fetch_assoc()) {
    $day = $row['day_of_week'];
    if (!array_key_exists($day, $schedule_by_day)) continue;
    $schedule_by_day[$day][] = [
        'subject_code'  => $row['subject_code'],
        'subject_name'  => $row['subject_name'],
        'units'         => $row['units'],
        'section'       => $row['section_name'],
        'section_code'  => $row['section_code'],
        'course'        => $row['course'],
        'year_level'    => $row['year_level'],
        'semester'      => $row['semester'],
        'school_year'   => $row['school_year'],
        'time'          => $row['start_time_fmt'] . ' - ' . $row['end_time_fmt'],
        'room'          => $row['room'] ?: 'TBA',
        'student_count' => (int)$row['student_count']
    ];
}

echo json_encode([
    'success'  => true,
    'schedule' => $schedule_by_day
]);

$conn->close();
?>
