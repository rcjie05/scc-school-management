<?php
require_once '../../config.php';
requireLogin();
header('Content-Type: application/json');

$conn = getDBConnection();
if (!$conn) { http_response_code(500); echo json_encode(['success'=>false,'message'=>'DB error']); exit(); }

// Check tables exist
$check = $conn->query("SHOW TABLES LIKE 'floorplan_3d_active'");
if ($check->num_rows === 0) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'No 3D layout published yet.']);
    exit();
}

$res = $conn->query("SELECT l.layout_json, l.name, l.updated_at
    FROM floorplan_3d_active a
    JOIN floorplan_3d_layouts l ON a.layout_id = l.id
    ORDER BY a.set_at DESC LIMIT 1");

if ($res->num_rows === 0) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'No active layout published yet. Ask an admin to publish one.']);
    exit();
}

$row = $res->fetch_assoc();
$conn->close();
echo json_encode(['success' => true, 'name' => $row['name'], 'updated_at' => $row['updated_at'], 'layout_json' => $row['layout_json']]);
