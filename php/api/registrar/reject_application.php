<?php
require_once '../../config.php';

// ── Dynamic school name & school year ────────────────────────────────
$_sn_conn = getDBConnection();
$_sn_res  = $_sn_conn ? $_sn_conn->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('school_name','current_school_year')") : false;
$school_name = 'My School';
$current_school_year = '----';
if ($_sn_res) { while ($_sn_row = $_sn_res->fetch_assoc()) { if ($_sn_row['setting_key']==='school_name') $school_name=$_sn_row['setting_value']; if ($_sn_row['setting_key']==='current_school_year') $current_school_year=$_sn_row['setting_value']; } }
// ──────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');

requireRole('registrar');

$input = json_decode(file_get_contents('php://input'), true);
$application_id = intval($input['application_id'] ?? 0);
$reason = sanitizeInput($input['reason'] ?? '');

if ($application_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Update application status
$stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $application_id);

if ($stmt->execute()) {
    // Create notification for student
    $message = 'Your enrollment application has been rejected.';
    if (!empty($reason)) {
        $message .= ' Reason: ' . $reason;
    }
    
    createNotification(
        $conn, 
        $application_id, 
        'Application Rejected', 
        $message
    );
    
    // Log action
    logAction($conn, $_SESSION['user_id'], 'Rejected student application', 'users', $application_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Application rejected'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to reject application']);
}

$conn->close();
?>
