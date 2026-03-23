<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');
$conn = getDBConnection();

$result = $conn->query("
    SELECT j.*, d.department_name,
           u.name AS posted_by_name,
           (SELECT COUNT(*) FROM hr_applicants a WHERE a.job_id = j.id) AS applicant_count
    FROM hr_job_postings j
    LEFT JOIN departments d ON j.department_id = d.id
    LEFT JOIN users u ON j.posted_by = u.id
    ORDER BY j.created_at DESC
");

$jobs = [];
while ($row = $result->fetch_assoc()) $jobs[] = $row;
echo json_encode(['success' => true, 'jobs' => $jobs]);
$conn->close();
?>
