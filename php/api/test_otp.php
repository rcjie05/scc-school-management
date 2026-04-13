<?php
/**
 * SMTP + OTP Debug Tool
 * Visit: https://your-railway-url/php/api/test_otp.php?email=test@gmail.com
 * DELETE this file after fixing the issue.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../smtp_mailer.php';

$result = [];

// ── 1. Env variables ──────────────────────────────────────────────────
$result['env'] = [
    'SMTP_USERNAME'     => getenv('SMTP_USERNAME')     ?: '❌ NOT SET',
    'SMTP_APP_PASSWORD' => getenv('SMTP_APP_PASSWORD') ? '✅ SET (' . strlen(getenv('SMTP_APP_PASSWORD')) . ' chars)' : '❌ NOT SET',
    'SMTP_PASSWORD'     => getenv('SMTP_PASSWORD')     ? '✅ SET (' . strlen(getenv('SMTP_PASSWORD')) . ' chars)' : '❌ NOT SET',
];

// ── 2. Constants resolved ─────────────────────────────────────────────
$result['smtp_config'] = [
    'SMTP_HOST'       => SMTP_HOST,
    'SMTP_PORT'       => SMTP_PORT,
    'SMTP_ENCRYPTION' => SMTP_ENCRYPTION,
    'SMTP_USERNAME'   => SMTP_USERNAME,
    'SMTP_PASSWORD'   => (SMTP_PASSWORD === 'REPLACE_WITH_YOUR_APP_PASSWORD')
                            ? '❌ STILL DEFAULT - not set in Railway!'
                            : '✅ SET (' . strlen(SMTP_PASSWORD) . ' chars)',
    'SMTP_FROM_EMAIL' => SMTP_FROM_EMAIL,
];

// ── 3. TCP connectivity to smtp.gmail.com:587 ────────────────────────
$result['tcp_connect'] = [];
$sock = @fsockopen('smtp.gmail.com', 587, $errno, $errstr, 10);
if ($sock) {
    $result['tcp_connect']['port_587'] = '✅ Connected';
    fclose($sock);
} else {
    $result['tcp_connect']['port_587'] = "❌ FAILED: $errstr ($errno) — Railway may be blocking outbound SMTP";
}

$sock2 = @fsockopen('ssl://smtp.gmail.com', 465, $errno2, $errstr2, 10);
if ($sock2) {
    $result['tcp_connect']['port_465'] = '✅ Connected';
    fclose($sock2);
} else {
    $result['tcp_connect']['port_465'] = "❌ FAILED: $errstr2 ($errno2)";
}

// ── 4. Actual send test (only if ?email= is provided) ─────────────────
$test_email = trim($_GET['email'] ?? '');
if ($test_email && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    try {
        $mailer = new SMTPMailer();
        $sent = $mailer->send(
            $test_email,
            'Test User',
            'SMTP Test from Railway',
            '<h2>It works!</h2><p>SMTP is configured correctly on Railway.</p>'
        );
        $result['send_test'] = $sent
            ? "✅ Email sent to $test_email — check your inbox!"
            : "❌ send() returned false — check error_log";
    } catch (Exception $e) {
        $result['send_test'] = "❌ Exception: " . $e->getMessage();
    }
} else {
    $result['send_test'] = 'ℹ️ Add ?email=you@gmail.com to the URL to send a real test email';
}

echo json_encode($result, JSON_PRETTY_PRINT);
