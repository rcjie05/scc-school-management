<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn     = getDBConnection();
$admin_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$type  = $input['type'] ?? '';
$id    = $input['id']   ?? '';

switch ($type) {

    case 'announcement':
        $nm = $conn->query("SELECT title FROM announcements WHERE id = " . intval($id))->fetch_assoc()['title'] ?? '';
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND deleted_at IS NOT NULL");
        $stmt->bind_param("i", $id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            logAction($conn, $admin_id, "Permanently deleted announcement: $nm", 'announcements', $id);
            echo json_encode(['success' => true, 'message' => "Announcement permanently deleted"]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed or item not in recycle bin']);
        }
        break;

    case 'grade_sheet':
        // Get file_path before deletion to remove disk file too
        $stmt = $conn->prepare("SELECT file_path FROM grade_submissions WHERE id = ? AND deleted_at IS NOT NULL");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Not found or not in recycle bin']);
            break;
        }
        // Delete disk file if present
        if (!empty($row['file_path']) && file_exists($row['file_path'])) {
            @unlink($row['file_path']);
        }
        $stmt = $conn->prepare("DELETE FROM grade_submissions WHERE id = ? AND deleted_at IS NOT NULL");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            logAction($conn, $admin_id, "Permanently deleted grade sheet id=$id", 'grade_submissions', $id);
            echo json_encode(['success' => true, 'message' => 'Grade sheet permanently deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
        break;

    case 'avatar':
        // id = full file path
        $file_path = $id;
        if (file_exists($file_path) && strpos($file_path, 'deleted_avatars') !== false) {
            @unlink($file_path);
            logAction($conn, $admin_id, "Permanently deleted avatar file: " . basename($file_path), 'users', 0);
            echo json_encode(['success' => true, 'message' => 'Avatar permanently deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'File not found in recycle bin']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown item type']);
}

$conn->close();
