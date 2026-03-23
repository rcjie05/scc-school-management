<?php
require_once '../../config.php';
requireRole('student');
header('Content-Type: application/json');

$conn = getDBConnection();

$department = isset($_GET['department']) ? $_GET['department'] : null;

$sql = "
    SELECT 
        id,
        name,
        email,
        department,
        office_location,
        office_hours,
        avatar_url,
        status
    FROM users
    WHERE role IN ('teacher', 'registrar')
    AND status = 'active'
";

$params = [];
$types = "";

if ($department) {
    $sql .= " AND department = ?";
    $params[] = $department;
    $types .= "s";
}

$sql .= " ORDER BY department, name";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$faculty = [];
while ($row = $result->fetch_assoc()) {
    $row['avatar_url'] = getAvatarUrl($row['avatar_url']);
    $faculty[] = $row;
}

$dept_stmt = $conn->prepare("SELECT DISTINCT department FROM users WHERE role IN ('teacher', 'registrar') AND department IS NOT NULL AND status = 'active' ORDER BY department");
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

$departments = [];
while ($dept = $dept_result->fetch_assoc()) {
    if ($dept['department']) {
        $departments[] = $dept['department'];
    }
}

echo json_encode([
    'success' => true,
    'faculty' => $faculty,
    'departments' => $departments,
    'total' => count($faculty)
]);

$conn->close();
?>
