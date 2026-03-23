<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('teacher');

$conn    = getDBConnection();
$user_id = $_SESSION['user_id'];
$input   = json_decode(file_get_contents('php://input'), true);

$name            = trim($input['name'] ?? '');
$email           = trim($input['email'] ?? '');
$office_location = trim($input['office_location'] ?? '');
$office_hours    = trim($input['office_hours'] ?? '');

if (empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check email uniqueness
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already taken']);
    exit;
}
$stmt->close();

$stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, office_location = ?, office_hours = ? WHERE id = ?");
$stmt->bind_param("ssssi", $name, $email, $office_location, $office_hours, $user_id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok, 'message' => $ok ? 'Profile updated' : 'Update failed']);
?>
