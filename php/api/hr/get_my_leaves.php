<?php
require_once '../../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher', 'registrar', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];
$year    = intval($_GET['year'] ?? date('Y'));

// My leave requests
$stmt = $conn->prepare("
    SELECT lr.id, lr.start_date, lr.end_date, lr.total_days, lr.reason,
           lr.status, lr.review_note, lr.reviewed_at, lr.created_at,
           lt.name as leave_type, lt.id as leave_type_id, lt.max_days_per_year
    FROM hr_leave_requests lr
    JOIN hr_leave_types lt ON lr.leave_type_id = lt.id
    WHERE lr.user_id = ? AND YEAR(lr.created_at) = ?
    ORDER BY lr.created_at DESC
");
$stmt->bind_param("ii", $user_id, $year);
$stmt->execute();
$result = $stmt->get_result();
$leaves = [];
while ($row = $result->fetch_assoc()) $leaves[] = $row;

// Leave balances for the year
$balStmt = $conn->prepare("
    SELECT lt.id, lt.name, lt.max_days_per_year,
           COALESCE(b.used_days, 0) as used_days
    FROM hr_leave_types lt
    LEFT JOIN hr_leave_balances b ON lt.id = b.leave_type_id AND b.user_id = ? AND b.year = ?
    WHERE lt.is_active = 1
");
$balStmt->bind_param("ii", $user_id, $year);
$balStmt->execute();
$balResult = $balStmt->get_result();
$balances  = [];
while ($row = $balResult->fetch_assoc()) $balances[] = $row;

// Leave types for the dropdown
$ltResult  = $conn->query("SELECT id, name, max_days_per_year, description FROM hr_leave_types WHERE is_active=1");
$leaveTypes = [];
while ($row = $ltResult->fetch_assoc()) $leaveTypes[] = $row;

echo json_encode(['success' => true, 'leaves' => $leaves, 'balances' => $balances, 'leave_types' => $leaveTypes]);
$conn->close();
?>
