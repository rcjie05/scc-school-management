<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn    = getDBConnection();
$hr_id   = $_SESSION['user_id'];
$input   = json_decode(file_get_contents('php://input'), true);
$records = $input['records'] ?? [];

if (empty($records)) {
    echo json_encode(['success' => false, 'message' => 'No records provided']);
    exit;
}

$saved = 0;
$errors = 0;

foreach ($records as $rec) {
    $user_id  = intval($rec['user_id'] ?? 0);
    $date     = $rec['date'] ?? null;
    $status   = $rec['status'] ?? 'present';
    $time_in  = !empty($rec['time_in'])  ? $rec['time_in']  : null;
    $time_out = !empty($rec['time_out']) ? $rec['time_out'] : null;
    $remarks  = sanitizeInput($rec['remarks'] ?? '');

    if (!$user_id || !$date) continue;

    // UPSERT using INSERT ... ON DUPLICATE KEY UPDATE
    $stmt = $conn->prepare("
        INSERT INTO hr_attendance (user_id, date, status, time_in, time_out, remarks, recorded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            status      = VALUES(status),
            time_in     = VALUES(time_in),
            time_out    = VALUES(time_out),
            remarks     = VALUES(remarks),
            recorded_by = VALUES(recorded_by),
            updated_at  = CURRENT_TIMESTAMP
    ");
    $stmt->bind_param("isssssi", $user_id, $date, $status, $time_in, $time_out, $remarks, $hr_id);

    if ($stmt->execute()) {
        $saved++;
    } else {
        $errors++;
    }
}

if ($errors === 0) {
    logAction($conn, $hr_id, "Saved attendance for date: " . ($records[0]['date'] ?? 'unknown') . " ($saved records)", 'hr_attendance');
    echo json_encode(['success' => true, 'message' => "Attendance saved for $saved employee(s)."]);
} else {
    echo json_encode(['success' => false, 'message' => "Saved $saved records. $errors failed."]);
}

$conn->close();
?>
