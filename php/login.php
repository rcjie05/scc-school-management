<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email    = sanitizeInput($_POST['email']    ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit();
}

// ── Rate Limiting: block brute force ─────────────────────────────────────────
$rateCheck = checkLoginRateLimit($email);
if (!$rateCheck['allowed']) {
    echo json_encode(['success' => false, 'message' => $rateCheck['message']]);
    exit();
}

// ── Database lookup ───────────────────────────────────────────────────────────
$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT id, name, email, password, role, status, course FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Always take the same time to respond (prevent timing attacks)
if ($result->num_rows === 0) {
    password_verify('dummy', '$2y$10$dummyhashfortimingnormalization'); // constant time
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();

// ── Password verification ─────────────────────────────────────────────────────
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $conn->close();
    exit();
}

// ── Account status checks ─────────────────────────────────────────────────────
if ($user['status'] === 'inactive') {
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact the administrator.']);
    $conn->close();
    exit();
}

if ($user['role'] === 'student' && $user['status'] === 'pending') {
    echo json_encode(['success' => false, 'message' => 'Your account is pending approval. Please wait for the registrar to approve your registration.']);
    $conn->close();
    exit();
}

if ($user['status'] === 'rejected') {
    echo json_encode(['success' => false, 'message' => 'Your account has been rejected. Please contact the registrar.']);
    $conn->close();
    exit();
}

// ── Login success — initialize secure session ─────────────────────────────────
clearLoginRateLimit($email);
initializeSession($user);

// Log the login with IP
logAction($conn, $user['id'], 'User logged in from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));

// ── Determine redirect ────────────────────────────────────────────────────────
$redirect = 'dashboard.php';
switch ($user['role']) {
    case 'student':   $redirect = 'student/dashboard.php';   break;
    case 'teacher':   $redirect = 'teacher/dashboard.php';   break;
    case 'registrar': $redirect = 'registrar/dashboard.php'; break;
    case 'admin':     $redirect = 'admin/dashboard.php';     break;
    case 'hr':        $redirect = 'hr/dashboard.php';        break;
}

echo json_encode([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => BASE_URL . '/' . $redirect,
    'user'     => ['name' => $user['name'], 'role' => $user['role']]
]);

$conn->close();
?>
