<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('registrar');

$conn   = getDBConnection();
$status = $_GET['status'] ?? '';

$where  = $status ? "WHERE adr.status = ?" : "";
$params = $status ? [$status] : [];
$types  = $status ? "s" : "";

$sql = "
    SELECT adr.id, adr.request_type, adr.reason, adr.status, adr.registrar_note,
           adr.created_at, adr.reviewed_at,
           s.subject_code, s.subject_name, s.units,
           u.name AS student_name, u.student_id AS student_no, u.course, u.year_level,
           sec.section_name, sec.section_code,
           rv.name AS reviewed_by_name
    FROM add_drop_requests adr
    JOIN subjects s ON adr.subject_id = s.id
    JOIN users u ON adr.student_id = u.id
    LEFT JOIN sections sec ON u.section_id = sec.id
    LEFT JOIN users rv ON adr.reviewed_by = rv.id
    $where
    ORDER BY FIELD(adr.status,'pending','approved','rejected'), adr.created_at DESC
";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $row['created_at']  = date('M d, Y h:i A', strtotime($row['created_at']));
    $row['reviewed_at'] = $row['reviewed_at'] ? date('M d, Y h:i A', strtotime($row['reviewed_at'])) : null;
    $requests[] = $row;
}

// Count pending
$cnt = $conn->query("SELECT COUNT(*) as c FROM add_drop_requests WHERE status='pending'");
$pending_count = $cnt->fetch_assoc()['c'];

echo json_encode(['success' => true, 'requests' => $requests, 'pending_count' => $pending_count]);
$conn->close();
?>
