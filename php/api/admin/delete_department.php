<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Department ID is required']);
    exit();
}

$dept_id = intval($input['department_id']);

$stmt = $conn->prepare("SELECT department_name FROM departments WHERE id = ?");
$stmt->bind_param("i", $dept_id);
$stmt->execute();
$dept = $stmt->get_result()->fetch_assoc();

if (!$dept) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit();
}

$stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
$stmt->bind_param("i", $dept_id);

if ($stmt->execute()) {
    logAction($conn, $admin_id, "Deleted department: {$dept['department_name']}", 'departments', $dept_id);
    echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete department: ' . $stmt->error]);
}

$conn->close();
?>
