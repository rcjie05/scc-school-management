<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $input['user_id'] ?? null;
    $status = $input['status'] ?? null;
    $deactivated_until = $input['deactivated_until'] ?? null;
    $deactivation_reason = $input['deactivation_reason'] ?? null;
    
    if (!$user_id || !$status) {
        throw new Exception('User ID and status are required');
    }
    
    // Validate status
    $allowed_statuses = ['active', 'inactive', 'pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception('Invalid status value');
    }
    
    $conn = getDBConnection();
    
    if ($status === 'inactive') {
        // Deactivating user - require reason and optional end date
        if (empty($deactivation_reason)) {
            throw new Exception('Deactivation reason is required');
        }
        
        // Update user status with deactivation details
        $stmt = $conn->prepare("UPDATE users SET status = ?, deactivated_until = ?, deactivation_reason = ? WHERE id = ?");
        $stmt->bind_param("sssi", $status, $deactivated_until, $deactivation_reason, $user_id);
        
        if ($stmt->execute()) {
            // Log the action
            $action = $deactivated_until 
                ? "User suspended until " . date('Y-m-d H:i', strtotime($deactivated_until)) 
                : "User deactivated indefinitely";
            logAction($conn, $_SESSION['user_id'], $action, 'users', $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => $deactivated_until 
                    ? "User suspended until " . date('M d, Y h:i A', strtotime($deactivated_until))
                    : "User deactivated successfully"
            ]);
        } else {
            throw new Exception('Failed to deactivate user');
        }
    } else {
        // Activating user - clear deactivation details
        $stmt = $conn->prepare("UPDATE users SET status = ?, deactivated_until = NULL, deactivation_reason = NULL WHERE id = ?");
        $stmt->bind_param("si", $status, $user_id);
        
        if ($stmt->execute()) {
            // Log the action
            logAction($conn, $_SESSION['user_id'], 'User activated/status changed', 'users', $user_id);
            
            echo json_encode([
                'success' => true,
                'message' => "User activated successfully"
            ]);
        } else {
            throw new Exception('Failed to update user status');
        }
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
