<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn = getDBConnection();

$result = $conn->query("
    SELECT 
        u.id, u.name, u.email, u.role, u.status as user_status,
        u.avatar_url, u.department, u.office_location, u.office_hours, u.created_at as joined_at,
        e.id as hr_id, e.employment_type, e.hire_date, e.salary_grade,
        e.monthly_salary, e.position, e.department_id, e.status as hr_status,
        e.sss_number, e.philhealth_number, e.pagibig_number, e.tin_number,
        e.emergency_contact_name, e.emergency_contact_phone, e.emergency_contact_relation,
        d.department_name as department_name
    FROM users u
    LEFT JOIN hr_employees e ON u.id = e.user_id
    LEFT JOIN departments d ON e.department_id = d.id
    WHERE u.role IN ('teacher','registrar','admin')
    ORDER BY u.name ASC
");

$employees = [];
while ($row = $result->fetch_assoc()) {
    // Fetch leave summary for this employee
    $leaveStmt = $conn->prepare("
        SELECT lt.name as leave_type, lr.status, lr.start_date, lr.end_date, lr.total_days
        FROM hr_leave_requests lr
        JOIN hr_leave_types lt ON lr.leave_type_id = lt.id
        WHERE lr.user_id = ? AND YEAR(lr.created_at) = YEAR(CURDATE())
        ORDER BY lr.created_at DESC
        LIMIT 5
    ");
    $leaveStmt->bind_param("i", $row['id']);
    $leaveStmt->execute();
    $leaveRes = $leaveStmt->get_result();
    $row['recent_leaves'] = [];
    while ($lr = $leaveRes->fetch_assoc()) $row['recent_leaves'][] = $lr;

    $row['avatar_url'] = getAvatarUrl($row['avatar_url'] ?? null);
    $employees[] = $row;
}

echo json_encode(['success' => true, 'employees' => $employees]);
$conn->close();
?>
