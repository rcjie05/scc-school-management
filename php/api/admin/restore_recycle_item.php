<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$type  = $input['type']  ?? '';
$id    = $input['id']    ?? '';

switch ($type) {

    case 'announcement':
        $stmt = $conn->prepare("UPDATE announcements SET deleted_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $nm = $conn->query("SELECT title FROM announcements WHERE id = $id")->fetch_assoc()['title'] ?? '';
            logAction($conn, $admin_id, "Restored announcement from recycle bin: $nm", 'announcements', $id);
            echo json_encode(['success' => true, 'message' => "Announcement '$nm' restored"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Restore failed']);
        }
        break;

    case 'grade_sheet':
        $stmt = $conn->prepare("UPDATE grade_submissions SET deleted_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            logAction($conn, $admin_id, "Restored grade sheet from recycle bin: id=$id", 'grade_submissions', $id);
            echo json_encode(['success' => true, 'message' => 'Grade sheet restored successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Restore failed']);
        }
        break;

    case 'avatar':
        // id = file path, user_id also passed
        $file_path = $id; // full path to deleted file
        $user_id   = intval($input['user_id'] ?? 0);
        if (!file_exists($file_path) || !$user_id) {
            echo json_encode(['success' => false, 'message' => 'File not found or invalid user']);
            break;
        }
        $projectRoot = dirname(__FILE__, 4);
        $avatarDir   = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars' . DIRECTORY_SEPARATOR;
        if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);

        $newName = basename($file_path);
        // Strip the user{id}_{ts}_ prefix to get original filename
        $newName = preg_replace('/^user\d+_\d+_/', '', $newName);
        $destPath = $avatarDir . $newName;

        // Compute web URL
        $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
        $projPath = rtrim(str_replace('\\', '/', $projectRoot), '/');
        $webPrefix = str_replace($docRoot, '', $projPath);
        $avatarUrl = $webPrefix . '/uploads/avatars/' . $newName;

        if (@rename($file_path, $destPath)) {
            $stmt = $conn->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $stmt->bind_param("si", $avatarUrl, $user_id);
            $stmt->execute();
            logAction($conn, $admin_id, "Restored avatar from recycle bin for user_id=$user_id", 'users', $user_id);
            echo json_encode(['success' => true, 'message' => 'Avatar restored successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to move file back']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown item type']);
}

$conn->close();
