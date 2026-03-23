<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();

// Get all buildings with room counts
$stmt = $conn->prepare("
    SELECT 
        b.id,
        b.building_name,
        b.building_code,
        b.description,
        b.location,
        COUNT(r.id) as room_count,
        DATE_FORMAT(b.created_at, '%M %d, %Y') as created_date
    FROM buildings b
    LEFT JOIN rooms r ON r.building_id = b.id
    GROUP BY b.id
    ORDER BY b.building_name
");

$stmt->execute();
$result = $stmt->get_result();

$buildings = [];
while ($row = $result->fetch_assoc()) {
    $buildings[] = $row;
}

echo json_encode([
    'success' => true,
    'buildings' => $buildings,
    'total' => count($buildings)
]);

$conn->close();
?>
