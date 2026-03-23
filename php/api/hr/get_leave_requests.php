<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn   = getDBConnection();
$status = $_GET['status'] ?? '';
$year   = intval($_GET['year'] ?? date('Y'));

$sql = "
    SELECT 
        lr.id, lr.start_date, lr.end_date, lr.total_days, lr.reason,
        lr.status, lr.review_note, lr.reviewed_at, lr.created_at,
        u.name as employee_name, u.role as employee_role,
        lt.name as leave_type, lt.id as leave_type_id,
        r.name as reviewed_by_name
    FROM hr_leave_requests lr
    JOIN users u ON lr.user_id = u.id
    JOIN hr_leave_types lt ON lr.leave_type_id = lt.id
    LEFT JOIN users r ON lr.reviewed_by = r.id
    WHERE YEAR(lr.created_at) = ?
";

$params = [$year];
$types  = "i";

if ($status) {
    $sql   .= " AND lr.status = ?";
    $params[] = $status;
    $types   .= "s";
}

$sql .= " ORDER BY lr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) $requests[] = $row;

// Leave type summary for the year
$leaveTypes = $conn->query("SELECT id, name, max_days_per_year FROM hr_leave_types WHERE is_active=1");
$leaveTypesArr = [];
while ($lt = $leaveTypes->fetch_assoc()) $leaveTypesArr[] = $lt;

echo json_encode(['success' => true, 'requests' => $requests, 'leave_types' => $leaveTypesArr]);
$conn->close();
?>
