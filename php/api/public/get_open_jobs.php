<?php
require_once '../../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn = getDBConnection();

$result = $conn->query("
    SELECT j.id, j.title, j.employment_type, j.slots, j.description, j.requirements, j.deadline,
           d.department_name
    FROM hr_job_postings j
    LEFT JOIN departments d ON j.department_id = d.id
    WHERE j.status = 'open'
    ORDER BY j.created_at DESC
");

$jobs = [];
while ($row = $result->fetch_assoc()) $jobs[] = $row;

echo json_encode(['success' => true, 'jobs' => $jobs]);
$conn->close();
?>
