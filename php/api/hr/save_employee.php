<?php
require_once '../../config.php';
requireRole('hr');
header('Content-Type: application/json');

$conn     = getDBConnection();
$hr_id = $_SESSION["user_id"];
$input    = json_decode(file_get_contents('php://input'), true);

$user_id                   = intval($input['user_id'] ?? 0);
$employment_type           = $input['employment_type'] ?? 'full_time';
$hire_date                 = $input['hire_date'] ?? null;
$salary_grade              = sanitizeInput($input['salary_grade'] ?? '');
$monthly_salary            = !empty($input['monthly_salary']) ? floatval($input['monthly_salary']) : null;
$position                  = sanitizeInput($input['position'] ?? '');
$department_id             = !empty($input['department_id']) ? intval($input['department_id']) : null;
$sss_number                = sanitizeInput($input['sss_number'] ?? '');
$philhealth_number         = sanitizeInput($input['philhealth_number'] ?? '');
$pagibig_number            = sanitizeInput($input['pagibig_number'] ?? '');
$tin_number                = sanitizeInput($input['tin_number'] ?? '');
$emergency_contact_name    = sanitizeInput($input['emergency_contact_name'] ?? '');
$emergency_contact_phone   = sanitizeInput($input['emergency_contact_phone'] ?? '');
$emergency_contact_relation= sanitizeInput($input['emergency_contact_relation'] ?? '');
$hr_status                 = $input['hr_status'] ?? 'active';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

// Check if HR profile exists
$existing = $conn->prepare("SELECT id FROM hr_employees WHERE user_id = ?");
$existing->bind_param("i", $user_id);
$existing->execute();
$exists = $existing->get_result()->fetch_assoc();

if ($exists) {
    $stmt = $conn->prepare("
        UPDATE hr_employees SET
            employment_type=?, hire_date=?, salary_grade=?, monthly_salary=?,
            position=?, department_id=?, sss_number=?, philhealth_number=?,
            pagibig_number=?, tin_number=?, emergency_contact_name=?,
            emergency_contact_phone=?, emergency_contact_relation=?, status=?
        WHERE user_id=?
    ");
    $stmt->bind_param("sssdsisssssssi",
        $employment_type, $hire_date, $salary_grade, $monthly_salary,
        $position, $department_id, $sss_number, $philhealth_number,
        $pagibig_number, $tin_number, $emergency_contact_name,
        $emergency_contact_phone, $emergency_contact_relation, $hr_status,
        $user_id
    );
} else {
    $stmt = $conn->prepare("
        INSERT INTO hr_employees
            (user_id, employment_type, hire_date, salary_grade, monthly_salary,
             position, department_id, sss_number, philhealth_number,
             pagibig_number, tin_number, emergency_contact_name,
             emergency_contact_phone, emergency_contact_relation, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param("isssdsissssssss",
        $user_id, $employment_type, $hire_date, $salary_grade, $monthly_salary,
        $position, $department_id, $sss_number, $philhealth_number,
        $pagibig_number, $tin_number, $emergency_contact_name,
        $emergency_contact_phone, $emergency_contact_relation, $hr_status
    );
}

if ($stmt->execute()) {
    logAction($conn, $hr_id, "Saved HR profile for user ID: $user_id", 'hr_employees', $user_id);
    echo json_encode(['success' => true, 'message' => 'HR profile saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save HR profile: ' . $conn->error]);
}

$conn->close();
?>
