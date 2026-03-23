<?php
// Quick test — visit this URL to confirm PHP is working
// http://localhost/school-mgmt-fixed/php/api/test_otp.php
// DELETE this file after confirming it works.

ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$conn = getDBConnection();
$result = [
    'php_ok'    => true,
    'php_ver'   => PHP_VERSION,
    'db_ok'     => ($conn !== null),
    'db_error'  => $conn ? null : 'Connection failed',
];

if ($conn) {
    // Check if OTP table exists
    $r = $conn->query("SHOW TABLES LIKE 'password_reset_otps'");
    $result['otp_table_exists'] = ($r && $r->num_rows > 0);

    // Check users table
    $r2 = $conn->query("SELECT COUNT(*) AS cnt FROM users");
    $result['users_count'] = $r2 ? $r2->fetch_assoc()['cnt'] : 'error';

    $conn->close();
}

echo json_encode($result, JSON_PRETTY_PRINT);
