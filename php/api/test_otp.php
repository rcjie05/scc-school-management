<?php
/**
 * Gmail API Debug Tool — DELETE after confirming emails work
 * Visit: https://your-railway-url/php/api/test_otp.php?email=you@gmail.com
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../gmail_mailer.php';

$result = [];
$result['env'] = [
    'GMAIL_CLIENT_ID'     => getenv('GMAIL_CLIENT_ID')     ? '✅ SET' : '❌ NOT SET',
    'GMAIL_CLIENT_SECRET' => getenv('GMAIL_CLIENT_SECRET') ? '✅ SET' : '❌ NOT SET',
    'GMAIL_REFRESH_TOKEN' => getenv('GMAIL_REFRESH_TOKEN') ? '✅ SET' : '❌ NOT SET',
    'GMAIL_FROM'          => getenv('GMAIL_FROM')          ?: '❌ NOT SET',
];

$test_email = trim($_GET['email'] ?? '');
if ($test_email && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    try {
        $mailer = new GmailMailer();
        $sent = $mailer->send($test_email, 'Test User', 'Gmail API Test', '<h2>✅ Gmail API works!</h2>');
        $result['send_test'] = $sent ? "✅ Sent to $test_email!" : "❌ Failed — check Railway logs";
    } catch (Exception $e) {
        $result['send_test'] = "❌ Exception: " . $e->getMessage();
    }
} else {
    $result['send_test'] = 'ℹ️ Add ?email=you@gmail.com to test';
}

echo json_encode($result, JSON_PRETTY_PRINT);
