<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get user by email
$stmt = $conn->prepare("SELECT id, name, email, password, role, status, course FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit();
}

$user = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit();
}

// Check if account is active (for non-students) or approved (for students)
if ($user['status'] === 'inactive') {
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact the administrator.']);
    exit();
}

if ($user['role'] === 'student' && $user['status'] === 'pending') {
    echo json_encode(['success' => false, 'message' => 'Your account is pending approval. Please wait for the registrar to approve your registration.']);
    exit();
}

if ($user['status'] === 'rejected') {
    echo json_encode(['success' => false, 'message' => 'Your account has been rejected. Please contact the registrar.']);
    exit();
}

// Regenerate session ID to prevent session fixation attacks
session_regenerate_id(true);

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['course'] = $user['course'] ?? '';

// Log the login
logAction($conn, $user['id'], 'User logged in');

// Determine redirect URL based on role
$redirect = 'dashboard.php';
switch ($user['role']) {
    case 'student':
        $redirect = 'student/dashboard.php';
        break;
    case 'teacher':
        $redirect = 'teacher/dashboard.php';
        break;
    case 'registrar':
        $redirect = 'registrar/dashboard.php';
        break;
    case 'admin':
        $redirect = 'admin/dashboard.php';
        break;
    case 'hr':
        $redirect = 'hr/dashboard.php';
        break;
}

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => $redirect,
    'user' => [
        'name' => $user['name'],
        'role' => $user['role']
    ]
]);

$conn->close();
?>