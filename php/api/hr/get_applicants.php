<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');
$conn = getDBConnection();

$result = $conn->query("
    SELECT a.*, j.title AS job_title, j.department_id
    FROM hr_applicants a
    JOIN hr_job_postings j ON a.job_id = j.id
    ORDER BY a.created_at DESC
");

$applicants = [];
while ($row = $result->fetch_assoc()) $applicants[] = $row;
echo json_encode(['success' => true, 'applicants' => $applicants]);
$conn->close();
?>
