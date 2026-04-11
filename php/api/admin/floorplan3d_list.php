<?php
require_once '../../config.php';
requireRole('admin');
header('Content-Type: application/json');

$conn = getDBConnection();
if (!$conn) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error']); exit(); }

// Ensure tables exist
$conn->query("CREATE TABLE IF NOT EXISTS `floorplan_3d_layouts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL DEFAULT 'Untitled Layout',
  `layout_json` LONGTEXT NOT NULL, `saved_by` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS `floorplan_3d_active` (
  `id` INT AUTO_INCREMENT PRIMARY KEY, `layout_id` INT NOT NULL, `set_by` INT NULL, `set_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $res = $conn->query("SELECT l.id, l.name, l.saved_by, l.created_at, l.updated_at,
        u.name AS saved_by_name,
        IF(a.layout_id IS NOT NULL, 1, 0) AS is_active
        FROM floorplan_3d_layouts l
        LEFT JOIN users u ON l.saved_by = u.id
        LEFT JOIN floorplan_3d_active a ON a.layout_id = l.id
        ORDER BY l.updated_at DESC");
    $layouts = [];
    while ($row = $res->fetch_assoc()) $layouts[] = $row;
    $conn->close();
    echo json_encode(['success' => true, 'layouts' => $layouts]);

} elseif ($action === 'load') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT layout_json FROM floorplan_3d_layouts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close(); $conn->close();
    if (!$row) { http_response_code(404); echo json_encode(['success'=>false,'message'=>'Not found']); exit(); }
    echo json_encode(['success' => true, 'layout_json' => $row['layout_json']]);
}
