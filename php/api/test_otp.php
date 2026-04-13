<?php
/**
 * Resend Debug Tool — verbose version
 * DELETE this file after confirming emails work.
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$result = [];

$result['env'] = [
    'RESEND_API_KEY' => getenv('RESEND_API_KEY') ? '✅ SET (' . strlen(getenv('RESEND_API_KEY')) . ' chars)' : '❌ NOT SET',
    'SMTP_USERNAME'  => getenv('SMTP_USERNAME') ?: '❌ NOT SET',
];

$test_email = trim($_GET['email'] ?? '');
$from_email = getenv('SMTP_USERNAME') ?: 'onboarding@resend.dev';
$from_name  = 'School Portal';

// Use Resend's default test sender if SMTP_USERNAME not set
$from = (getenv('SMTP_USERNAME') && strpos(getenv('SMTP_USERNAME'), '@') !== false)
    ? $from_name . ' <' . getenv('SMTP_USERNAME') . '>'
    : 'School Portal <onboarding@resend.dev>';

$result['sending_from'] = $from;

if ($test_email && filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
    $api_key = getenv('RESEND_API_KEY');

    $payload = json_encode([
        'from'    => $from,
        'to'      => [$test_email],
        'subject' => 'Resend Test from Railway',
        'html'    => '<h2>It works!</h2><p>Resend is working on Railway.</p>',
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $api_key,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    $result['http_code']      = $http_code;
    $result['resend_response'] = json_decode($response, true) ?: $response;
    $result['curl_error']     = $curl_err ?: null;

    if ($http_code === 200 || $http_code === 201) {
        $result['send_test'] = "✅ Email sent to $test_email — check your inbox!";
    } else {
        $result['send_test'] = "❌ Failed — see resend_response above for the exact error";
    }
} else {
    $result['send_test'] = 'ℹ️ Add ?email=you@gmail.com to the URL to send a real test email';
}

echo json_encode($result, JSON_PRETTY_PRINT);
