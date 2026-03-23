<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn     = getDBConnection();
$hr_id = $_SESSION["user_id"];
$input    = json_decode(file_get_contents('php://input'), true);

$leave_id    = intval($input['leave_id'] ?? 0);
$action      = $input['action'] ?? ''; // 'approved' or 'rejected'
$review_note = sanitizeInput($input['review_note'] ?? '');

if (!$leave_id || !in_array($action, ['approved', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get the leave request
$stmt = $conn->prepare("SELECT lr.*, u.name as employee_name, lt.name as leave_type FROM hr_leave_requests lr JOIN users u ON lr.user_id=u.id JOIN hr_leave_types lt ON lr.leave_type_id=lt.id WHERE lr.id=?");
$stmt->bind_param("i", $leave_id);
$stmt->execute();
$leave = $stmt->get_result()->fetch_assoc();

if (!$leave) {
    echo json_encode(['success' => false, 'message' => 'Leave request not found']);
    exit;
}

// Update status
$stmt = $conn->prepare("UPDATE hr_leave_requests SET status=?, reviewed_by=?, review_note=?, reviewed_at=NOW() WHERE id=?");
$stmt->bind_param("sisi", $action, $hr_id, $review_note, $leave_id);

if ($stmt->execute()) {
    // Update leave balance if approved
    if ($action === 'approved') {
        $year = date('Y', strtotime($leave['start_date']));
        $conn->query("INSERT INTO hr_leave_balances (user_id, leave_type_id, year, allocated_days, used_days)
            VALUES ({$leave['user_id']}, {$leave['leave_type_id']}, $year, 0, {$leave['total_days']})
            ON DUPLICATE KEY UPDATE used_days = used_days + {$leave['total_days']}");

        // Notify employee
        createNotification($conn, $leave['user_id'],
            "Leave Request Approved ✅",
            "Your {$leave['leave_type']} ({$leave['start_date']} to {$leave['end_date']}) has been approved." . ($review_note ? " Note: $review_note" : "")
        );
    } else {
        createNotification($conn, $leave['user_id'],
            "Leave Request Rejected ❌",
            "Your {$leave['leave_type']} ({$leave['start_date']} to {$leave['end_date']}) was rejected." . ($review_note ? " Reason: $review_note" : "")
        );
    }

    logAction($conn, $hr_id, ucfirst($action) . " leave request for {$leave['employee_name']}", 'hr_leave_requests', $leave_id);
    echo json_encode(['success' => true, 'message' => "Leave request $action successfully"]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update leave request']);
}

$conn->close();
?>
