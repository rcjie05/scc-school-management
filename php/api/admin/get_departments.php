<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();

// Get all departments
$stmt = $conn->prepare("
    SELECT 
        id,
        department_name,
        department_code,
        head_of_department,
        office_location,
        contact_email,
        contact_phone,
        DATE_FORMAT(created_at, '%M %d, %Y') as created_date
    FROM departments
    ORDER BY department_name
");

$stmt->execute();
$result = $stmt->get_result();

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

echo json_encode([
    'success' => true,
    'departments' => $departments,
    'total' => count($departments)
]);

$conn->close();
?>
