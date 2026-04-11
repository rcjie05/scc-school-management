<?php
require_once '../../config.php';
requireRole('admin');
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['layout_json'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing layout data.']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
    exit();
}

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS `floorplan_3d_layouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL DEFAULT 'Untitled Layout',
  `layout_json` LONGTEXT NOT NULL,
  `saved_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS `floorplan_3d_active` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `layout_id` INT NOT NULL,
  `set_by` INT NULL,
  `set_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$name       = trim($input['name'] ?? 'Untitled Layout');
$layoutJson = $input['layout_json'];
$userId     = $_SESSION['user_id'];
$action     = $input['action'] ?? 'save'; // save | update | set_active | delete

if ($action === 'save') {
    $stmt = $conn->prepare("INSERT INTO floorplan_3d_layouts (name, layout_json, saved_by) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $name, $layoutJson, $userId);
    $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'id' => $newId, 'message' => "Layout \"$name\" saved."]);

} elseif ($action === 'update') {
    $id = intval($input['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE floorplan_3d_layouts SET name=?, layout_json=?, updated_at=NOW() WHERE id=?");
    $stmt->bind_param("ssi", $name, $layoutJson, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'message' => "Layout updated."]);

} elseif ($action === 'set_active') {
    $id = intval($input['id'] ?? 0);
    $conn->query("TRUNCATE TABLE floorplan_3d_active");
    $stmt = $conn->prepare("INSERT INTO floorplan_3d_active (layout_id, set_by) VALUES (?, ?)");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Active layout updated.']);

} elseif ($action === 'delete') {
    $id = intval($input['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM floorplan_3d_layouts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    // Also clear active if it was this one
    $conn->query("DELETE FROM floorplan_3d_active WHERE layout_id=$id");
    $conn->close();
    echo json_encode(['success' => true, 'message' => 'Layout deleted.']);

} else {
    $conn->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
