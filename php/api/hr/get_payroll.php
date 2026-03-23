<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn  = getDBConnection();
$month = $_GET['month'] ?? null;
$role  = $_GET['role']  ?? '';

if (!$month) {
    echo json_encode(['success' => false, 'message' => 'Month required']);
    exit;
}

[$yr, $mo]    = explode('-', $month);
$payrollMonth = "$yr-$mo-01";

$roleFilter = '';
$params     = [$payrollMonth];
$types      = 's';

if ($role) {
    $roleFilter = "AND u.role = ?";
    $params[]   = $role;
    $types     .= 's';
}

$sql = "
    SELECT
        u.id, u.name, u.email, u.role, u.avatar_url,
        e.position, e.monthly_salary,
        p.id           AS payroll_id,
        p.basic_salary, p.days_worked, p.days_absent,
        p.overtime_hours, p.overtime_pay, p.allowances, p.gross_pay,
        p.sss_deduction, p.philhealth_deduction, p.pagibig_deduction,
        p.tax_deduction, p.other_deductions, p.total_deductions, p.net_pay,
        p.status       AS payroll_status,
        p.remarks
    FROM users u
    LEFT JOIN hr_employees e ON u.id = e.user_id
    LEFT JOIN hr_payroll   p ON u.id = p.user_id AND p.payroll_month = ?
    WHERE u.role IN ('teacher','registrar','admin') $roleFilter
    ORDER BY u.name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$employees = [];
while ($r = $res->fetch_assoc()) {
    $r['avatar_url'] = getAvatarUrl($r['avatar_url'] ?? null);
    $employees[] = $r;
}

echo json_encode(['success' => true, 'employees' => $employees]);
$conn->close();
?>
