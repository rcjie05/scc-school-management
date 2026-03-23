<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];
$input    = json_decode(file_get_contents('php://input'), true);
$course_id = intval($input['course_id'] ?? 0);

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Course ID required.']);
    exit();
}

// Get course name first
$chk = $conn->prepare("SELECT course_name FROM courses WHERE id=?");
$chk->bind_param('i', $course_id);
$chk->execute();
$row = $chk->get_result()->fetch_assoc();
if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Course not found.']);
    exit();
}

// Check if any sections or students are using it
$sec_chk = $conn->prepare("SELECT COUNT(*) as c FROM sections WHERE course = ?");
$sec_chk->bind_param('s', $row['course_name']);
$sec_chk->execute();
$sec_count = $sec_chk->get_result()->fetch_assoc()['c'];

$stu_chk = $conn->prepare("SELECT COUNT(*) as c FROM users WHERE course = ? AND role = 'student'");
$stu_chk->bind_param('s', $row['course_name']);
$stu_chk->execute();
$stu_count = $stu_chk->get_result()->fetch_assoc()['c'];

if ($sec_count > 0 || $stu_count > 0) {
    echo json_encode([
        'success' => false,
        'message' => "Cannot delete: {$row['course_name']} is used by $sec_count section(s) and $stu_count student(s). Consider setting it to Inactive instead."
    ]);
    exit();
}

$del = $conn->prepare("DELETE FROM courses WHERE id=?");
$del->bind_param('i', $course_id);

if ($del->execute()) {
    logAction($conn, $admin_id, "Deleted course: {$row['course_name']}", 'courses', $course_id);
    echo json_encode(['success' => true, 'message' => "Course '{$row['course_name']}' deleted successfully."]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete course.']);
}

$conn->close();
?>
