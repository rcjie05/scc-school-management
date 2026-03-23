<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input   = json_decode(file_get_contents('php://input'), true);
$user_id = intval($input['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit();
}

$stmt = $conn->prepare("SELECT id, name, avatar_url FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || empty($user['avatar_url'])) {
    echo json_encode(['success' => false, 'message' => 'User has no avatar to delete']);
    exit();
}

// Move file to deleted_avatars/ folder instead of permanent delete
// avatar_url is stored as a clean relative path e.g. "uploads/avatars/avatar_1_123.jpg"
$projectRoot = dirname(__DIR__, 3); // 3 levels up from php/api/admin/
$oldRelative = ltrim($user['avatar_url'], '/');
if (strpos($oldRelative, 'uploads/') !== false) {
    $oldRelative = 'uploads/' . substr($oldRelative, strpos($oldRelative, 'uploads/') + 8);
}
$srcPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $oldRelative);

$deletedDir  = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'deleted_avatars' . DIRECTORY_SEPARATOR;
if (!is_dir($deletedDir)) mkdir($deletedDir, 0755, true);

$filename    = basename($srcPath);
$destPath    = $deletedDir . 'user' . $user_id . '_' . time() . '_' . $filename;

$moved = false;
if (file_exists($srcPath)) {
    $moved = @rename($srcPath, $destPath);
}

// Clear avatar_url from DB regardless of whether file was moved
$stmt = $conn->prepare("UPDATE users SET avatar_url = NULL WHERE id = ?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    logAction($conn, $admin_id, "Soft-deleted avatar for user: {$user['name']}" . ($moved ? " (file moved to recycle bin)" : " (file not found on disk)"), 'users', $user_id);
    echo json_encode(['success' => true, 'message' => "Avatar for '{$user['name']}' removed" . ($moved ? ' and saved to recycle bin' : '')]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to remove avatar']);
}
$conn->close();
