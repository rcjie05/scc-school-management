<?php
// ── Environment ───────────────────────────────────────────────────────────────
// LOCAL TESTING ONLY — remove before pushing to GitHub!
// putenv('GROQ_API_KEY=your_key_here');

// Database configuration
// Supports Railway.app env variables and XAMPP local fallback
define('DB_HOST', getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('MYSQLPORT')     ?: getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'school_management');


// ── Base URL (works on both XAMPP and Railway) ────────────────────────────────
function getBaseUrl() {
    $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $webPrefix   = str_replace($docRoot, '', $projectRoot);
    return $webPrefix ?: '';
}
define('BASE_URL', getBaseUrl());

// ── Secure Session ────────────────────────────────────────────────────────────
require_once __DIR__ . '/session.php';
startSecureSession();

// ── Database Connection ───────────────────────────────────────────────────────
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

// ── Auth Helpers ──────────────────────────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && validateSession();
}

function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function checkUserStatus() {
    if (!isLoggedIn()) return false;

    $conn = getDBConnection();
    if (!$conn) return false;

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

    if ($user['status'] === 'inactive') {
        if ($user['deactivated_until']) {
            $deactivated_until = strtotime($user['deactivated_until']);
            if (time() >= $deactivated_until) {
                $update_stmt = $conn->prepare("UPDATE users SET status = 'active', deactivated_until = NULL, deactivation_reason = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $update_stmt->close();
                logAction($conn, $user_id, 'User auto-reactivated (suspension expired)', 'users', $user_id);
                $conn->close();
                return true;
            }
        }
        $conn->close();
        return false;
    }

    $conn->close();
    return true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Clear any stale session data
        destroySession();
        header('Location: ' . BASE_URL . '/login.html?error=session_expired');
        exit();
    }
    if (!checkUserStatus()) {
        destroySession();
        header('Location: ' . BASE_URL . '/login.html?error=deactivated');
        exit();
    }
}

function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        // Log unauthorized access attempt
        error_log("[SECURITY] Unauthorized role access: user_id=" . ($_SESSION['user_id'] ?? 'unknown') . " tried to access role=$role");
        header('Location: ' . BASE_URL . '/login.html?error=unauthorized');
        exit();
    }
}

// ── Input Sanitization ────────────────────────────────────────────────────────
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ── Notifications & Logging ───────────────────────────────────────────────────
function createNotification($conn, $user_id, $title, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $title, $message);
    return $stmt->execute();
}

function logAction($conn, $user_id, $action, $table_name = null, $record_id = null) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $user_id, $action, $table_name, $record_id);
    return $stmt->execute();
}

// ── Avatar URL Helper ─────────────────────────────────────────────────────────
function getAvatarUrl($stored) {
    if (empty($stored)) return null;
    if (strpos($stored, 'http') === 0) return $stored;
    $relative = ltrim($stored, '/');
    if (strpos($relative, 'uploads/') !== false) {
        $relative = 'uploads/' . substr($relative, strpos($relative, 'uploads/') + 8);
    }
    $docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $webPrefix   = str_replace($docRoot, '', $projectRoot);
    return $webPrefix . '/' . $relative;
}

// ── System Settings ───────────────────────────────────────────────────────────
function getSystemSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) return $row['setting_value'];
    return null;
}

// ── SMTP Mail Configuration ───────────────────────────────────────────────────
define('SMTP_HOST',       'smtp.gmail.com');
define('SMTP_PORT',       587);
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_USERNAME',   getenv('SMTP_USERNAME') ?: 'godzdemonz05@gmail.com');
define('SMTP_PASSWORD',   getenv('SMTP_APP_PASSWORD') ?: 'REPLACE_WITH_YOUR_APP_PASSWORD');
define('SMTP_FROM_EMAIL', getenv('SMTP_USERNAME') ?: 'godzdemonz05@gmail.com');
define('SMTP_FROM_NAME',  "St. Cecilia's College-Cebu");
