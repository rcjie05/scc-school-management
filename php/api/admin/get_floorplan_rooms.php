<?php
/**
 * Get all rooms with canvas layout data for the floor plan map.
 * Accessible by all logged-in users.
 */
require_once '../../config.php';
requireLogin();
header('Content-Type: application/json');

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT
        r.id,
        r.room_number  AS name,
        b.building_name,
        r.floor,
        r.room_type,
        r.x_pos,
        r.y_pos,
        r.width,
        r.height,
        r.color,
        r.capacity,
        r.purpose,
        r.image_url
    FROM rooms r
    JOIN buildings b ON b.id = r.building_id
    WHERE r.x_pos IS NOT NULL
      AND r.y_pos IS NOT NULL
      AND r.width  IS NOT NULL
      AND r.height IS NOT NULL
    ORDER BY r.y_pos, r.x_pos
");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = [
        'id'           => (int)$row['id'],
        'name'         => $row['name'],
        'building'     => $row['building_name'],
        'floor'        => $row['floor'],
        'type'         => $row['room_type'],
        'x'            => (int)$row['x_pos'],
        'y'            => (int)$row['y_pos'],
        'width'        => (int)$row['width'],
        'height'       => (int)$row['height'],
        'color'        => $row['color'] ?? '#85C1E2',
        'centerX'      => (int)$row['x_pos'] + (int)round($row['width']  / 2),
        'centerY'      => (int)$row['y_pos'] + (int)round($row['height'] / 2),
        'capacity'     => $row['capacity'],
        'purpose'      => $row['purpose'],
        'image_url'    => getAvatarUrl($row['image_url']),
    ];
}

echo json_encode([
    'success' => true,
    'rooms'   => $rooms,
    'total'   => count($rooms)
]);

$conn->close();
?>
