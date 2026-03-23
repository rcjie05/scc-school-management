<?php
require_once '../../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher', 'registrar', 'admin'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];
$input   = json_decode(file_get_contents('php://input'), true);
$leave_id = intval($input['leave_id'] ?? 0);

if (!$leave_id) {
    echo json_encode(['success' => false, 'message' => 'Leave ID required']);
    exit;
}

// Only allow cancelling own pending requests
$stmt = $conn->prepare("SELECT id, status FROM hr_leave_requests WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $leave_id, $user_id);
$stmt->execute();
$leave = $stmt->get_result()->fetch_assoc();

if (!$leave) {
    echo json_encode(['success' => false, 'message' => 'Leave request not found']);
    exit;
}
if ($leave['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Only pending requests can be cancelled']);
    exit;
}

$stmt = $conn->prepare("UPDATE hr_leave_requests SET status='cancelled' WHERE id=?");
$stmt->bind_param("i", $leave_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Leave request cancelled']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel leave request']);
}

$conn->close();
?>
