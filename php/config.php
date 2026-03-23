<?php
// Database configuration
// Supports Railway.app env variables and XAMPP local fallback
define('DB_HOST', getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'school_management');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper function to check if user status is active
function checkUserStatus() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        return false;
    }
    
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT status, deactivated_until FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $conn->close();
        return false;
    }
    
    $user = $result->fetch_assoc();
    
    // Check if user is inactive
    if ($user['status'] === 'inactive') {
        // Check if suspension has expired
        if ($user['deactivated_until']) {
            $deactivated_until = strtotime($user['deactivated_until']);
            $now = time();
            
            if ($now >= $deactivated_until) {
                // Suspension expired - auto-reactivate
                $update_stmt = $conn->prepare("UPDATE users SET status = 'active', deactivated_until = NULL, deactivation_reason = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Log the auto-reactivation
                logAction($conn, $user_id, 'User auto-reactivated (suspension expired)', 'users', $user_id);
                
                $conn->close();
                return true; // User is now active
            }
        }
        
        // User is still suspended
        $conn->close();
        return false;
    }
    
    $conn->close();
    return true;
}

// Helper function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.html');
        exit();
    }
    
    // Check if user is inactive
    if (!checkUserStatus()) {
        // Clear session
        session_unset();
        session_destroy();
        header('Location: /login.html?error=deactivated');
        exit();
    }
}

// Helper function to redirect if wrong role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /dashboard.php');
        exit();
    }
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Helper function to create notification
function createNotification($conn, $user_id, $title, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $message);
    return $stmt->execute();
}

// Helper function to log action
function logAction($conn, $user_id, $action, $table_name = null, $record_id = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $action, $table_name, $record_id);
    return $stmt->execute();
}

// ── Avatar URL helper ────────────────────────────────────────────────
// Converts a stored avatar_url (relative OR old absolute path) into
// the correct browser-accessible URL regardless of folder name.
function getAvatarUrl($stored) {
    if (empty($stored)) return null;
    if (strpos($stored, 'http') === 0) return $stored;

    // Normalise to a relative path (strip any leading slash or old prefix)
    $relative = ltrim($stored, '/');
    // If stored with an old project-folder prefix like "school-mgmt-fixed/uploads/..."
    // strip everything up to and including the first "uploads/" occurrence
    if (strpos($relative, 'uploads/') !== false) {
        $relative = 'uploads/' . substr($relative, strpos($relative, 'uploads/') + 8);
    }

    // Compute current web prefix (e.g. "/school-mgmt-fixed" or "")
    $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $webPrefix   = str_replace($docRoot, '', $projectRoot);

    return $webPrefix . '/' . $relative;
}

// System settings
function getSystemSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}
// ── SMTP Mail Configuration ──────────────────────────────────────────────────
// Gmail setup:
//   1. Go to myaccount.google.com → Security → 2-Step Verification (enable it)
//   2. Then go to: myaccount.google.com/apppasswords
//   3. Create an App Password → select "Mail" → copy the 16-char password
//   4. Set it as an environment variable SMTP_APP_PASSWORD on your server, OR
//      paste it below (not recommended for production / version-controlled repos)

define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_ENCRYPTION', 'tls');           // 'tls' for port 587, 'ssl' for port 465
define('SMTP_USERNAME',   getenv('SMTP_USERNAME') ?: 'godzdemonz05@gmail.com');
define('SMTP_PASSWORD',   getenv('SMTP_APP_PASSWORD') ?: 'REPLACE_WITH_YOUR_APP_PASSWORD');
define('SMTP_FROM_EMAIL', getenv('SMTP_USERNAME') ?: 'godzdemonz05@gmail.com');
define('SMTP_FROM_NAME',  "St. Cecilia's College-Cebu");
