<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn   = getDBConnection();
$status = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where  = ['1=1'];
$params = [];
$types  = '';

if ($status) { $where[] = 'c.status = ?'; $params[] = $status; $types .= 's'; }
if ($search) {
    $like = "%$search%";
    $where[] = '(c.course_name LIKE ? OR c.course_code LIKE ?)';
    $params[] = $like; $params[] = $like;
    $types .= 'ss';
}

$whereStr = implode(' AND ', $where);

$sql = "
    SELECT c.id, c.course_name, c.course_code, c.description,
           c.duration_years, c.total_units, c.status,
           c.department_id,
           d.department_name,
           DATE_FORMAT(c.created_at, '%M %d, %Y') AS created_date,
           (SELECT COUNT(*) FROM sections s WHERE s.course = c.course_name) AS section_count,
           (SELECT COUNT(*) FROM users u WHERE u.course = c.course_name AND u.role = 'student') AS student_count,
           (SELECT COUNT(*) FROM subjects sub WHERE sub.course = c.course_name) AS subject_count
    FROM courses c
    LEFT JOIN departments d ON c.department_id = d.id
    WHERE $whereStr
    ORDER BY c.status DESC, c.course_name
";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) $courses[] = $row;

// Count totals
$total_active   = 0;
$total_inactive = 0;
foreach ($courses as $c) {
    if ($c['status'] === 'active') $total_active++;
    else $total_inactive++;
}

echo json_encode([
    'success'        => true,
    'courses'        => $courses,
    'total'          => count($courses),
    'total_active'   => $total_active,
    'total_inactive' => $total_inactive
]);

$conn->close();
?>
