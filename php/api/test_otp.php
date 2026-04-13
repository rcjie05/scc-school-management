<?php
/**
 * SMTP + Resend Debug Tool
 * Visit: https://your-railway-url/php/api/test_otp.php
 * Send test: https://your-railway-url/php/api/test_otp.php?email=you@gmail.com
 * DELETE this file after confirming emails work.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../resend_mailer.php';

$result = [];

// ── 1. Env variables ──────────────────────────────────────────────────
$result['env'] = [
    'SMTP_USERNAME'     => getenv('SMTP_USERNAME')     ?: '❌ NOT SET',
    'SMTP_APP_PASSWORD' => getenv('SMTP_APP_PASSWORD') ? '✅ SET (' . strlen(getenv('SMTP_APP_PASSWORD')) . ' chars)' : '❌ NOT SET',
    'RESEND_API_KEY'    => getenv('RESEND_API_KEY')    ? '✅ SET (' . strlen(getenv('RESEND_API_KEY')) . ' chars)' : '❌ NOT SET — add this in Railway Variables!',
];

// ── 2. Resend send test ───────────────────────────────────────────────
$test_email = trim($_GET['email'] ?? '');
if ($test_email && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    try {
        $mailer = new ResendMailer();
        $sent = $mailer->send(
            $test_email,
            'Test User',
            'Resend Test from Railway',
            '<h2>✅ It works!</h2><p>Resend is configured correctly on Railway.</p>'
        );
        $result['send_test'] = $sent
            ? "✅ Email sent to $test_email — check your inbox!"
            : "❌ send() returned false — check RESEND_API_KEY in Railway Variables";
    } catch (Exception $e) {
        $result['send_test'] = "❌ Exception: " . $e->getMessage();
    }
} else {
    $result['send_test'] = 'ℹ️ Add ?email=you@gmail.com to the URL to send a real test email';
}

echo json_encode($result, JSON_PRETTY_PRINT);
