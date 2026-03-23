<?php
require_once '../../config.php';
header('Content-Type: application/json');
requireRole('admin');

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Handle GET request - fetch settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT setting_key, setting_value, description FROM system_settings ORDER BY setting_key");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = [
            'value' => $row['setting_value'],
            'description' => $row['description']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
    exit();
}

// Handle POST request - update settings
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['settings']) || !is_array($input['settings'])) {
    echo json_encode(['success' => false, 'message' => 'Settings data is required']);
    exit();
}

$updated = 0;
foreach ($input['settings'] as $key => $value) {
    $stmt = $conn->prepare("
        UPDATE system_settings 
        SET setting_value = ? 
        WHERE setting_key = ?
    ");
    $stmt->bind_param("ss", $value, $key);
    if ($stmt->execute()) {
        $updated++;
    }
}

if ($updated > 0) {
    logAction($conn, $admin_id, "Updated $updated system settings", 'system_settings', null);
    echo json_encode([
        'success' => true,
        'message' => "$updated settings updated successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No settings were updated'
    ]);
}

$conn->close();
?>
