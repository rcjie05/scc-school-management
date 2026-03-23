<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn    = getDBConnection();
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null;

if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit();
}

// Verify room exists
$chk = $conn->prepare("SELECT id, image_url FROM rooms WHERE id = ?");
$chk->bind_param("i", $room_id);
$chk->execute();
$row = $chk->get_result()->fetch_assoc();
$chk->close();
if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Room not found']);
    exit();
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $code = isset($_FILES['image']) ? $_FILES['image']['error'] : 'none';
    echo json_encode(['success' => false, 'message' => "Upload error (code: $code)"]);
    exit();
}

$file    = $_FILES['image'];
$maxSize = 5 * 1024 * 1024; // 5 MB
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB.']);
    exit();
}

$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
    exit();
}

if (!@getimagesize($file['tmp_name'])) {
    echo json_encode(['success' => false, 'message' => 'File does not appear to be a valid image.']);
    exit();
}

$projectRoot = dirname(__DIR__, 3); // php/api/admin -> root
$uploadDir   = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'rooms' . DIRECTORY_SEPARATOR;

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Delete old image if exists
if (!empty($row['image_url'])) {
    $oldPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($row['image_url'], '/'));
    if (file_exists($oldPath)) @unlink($oldPath);
}

$mimeExts = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$ext      = $mimeExts[$mimeType] ?? 'jpg';
$filename = 'room_' . $room_id . '_' . time() . '.' . $ext;
$savePath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $savePath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file. Check uploads/rooms/ permissions.']);
    exit();
}

$relative = 'uploads/rooms/' . $filename;

// Ensure column exists
$conn->query("ALTER TABLE rooms ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL");

$stmt = $conn->prepare("UPDATE rooms SET image_url = ? WHERE id = ?");
$stmt->bind_param("si", $relative, $room_id);
if (!$stmt->execute()) {
    @unlink($savePath);
    echo json_encode(['success' => false, 'message' => 'DB update failed: ' . $conn->error]);
    exit();
}
$stmt->close();

logAction($conn, $_SESSION['user_id'], "Uploaded image for room ID $room_id", 'rooms', $room_id);
$conn->close();

// Build web-accessible URL using the same helper as avatars
$imageUrl = getAvatarUrl($relative);

echo json_encode([
    'success'   => true,
    'message'   => 'Room image uploaded successfully!',
    'image_url' => $imageUrl,
]);
?>
