<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['building_id'])) {
    echo json_encode(['success' => false, 'message' => 'Building ID is required']);
    exit();
}

$building_id = intval($input['building_id']);

// Get building info for logging
$stmt = $conn->prepare("SELECT building_name FROM buildings WHERE id = ?");
$stmt->bind_param("i", $building_id);
$stmt->execute();
$building = $stmt->get_result()->fetch_assoc();

if (!$building) {
    echo json_encode(['success' => false, 'message' => 'Building not found']);
    exit();
}

// Delete building (cascade will handle rooms)
$stmt = $conn->prepare("DELETE FROM buildings WHERE id = ?");
$stmt->bind_param("i", $building_id);

if ($stmt->execute()) {
    logAction($conn, $admin_id, "Deleted building: {$building['building_name']}", 'buildings', $building_id);
    echo json_encode(['success' => true, 'message' => 'Building deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete building: ' . $stmt->error]);
}

$conn->close();
?>
