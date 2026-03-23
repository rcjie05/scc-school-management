<?php
require_once '../../config.php';

header('Content-Type: application/json');

requireRole('registrar');

$input = json_decode(file_get_contents('php://input'), true);
$application_id = intval($input['application_id'] ?? 0);

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
$stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $application_id);

if ($stmt->execute()) {
    // Create notification for student
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    createNotification(
        $conn, 
        $application_id, 
        'Application Approved', 
        'Your enrollment application has been approved! You can now proceed with subject enrollment.'
    );
    
    // Log action
    logAction($conn, $_SESSION['user_id'], 'Approved student application', 'users', $application_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Application approved successfully'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to approve application']);
}

$conn->close();
?>
