<?php
/**
 * Gmail API Debug Tool — DELETE after confirming emails work
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$result = [];
$result['env'] = [
    'GMAIL_CLIENT_ID'     => getenv('GMAIL_CLIENT_ID')     ? '✅ SET' : '❌ NOT SET',
    'GMAIL_CLIENT_SECRET' => getenv('GMAIL_CLIENT_SECRET') ? '✅ SET' : '❌ NOT SET',
    'GMAIL_REFRESH_TOKEN' => getenv('GMAIL_REFRESH_TOKEN') ? '✅ SET (' . strlen(getenv('GMAIL_REFRESH_TOKEN')) . ' chars)' : '❌ NOT SET',
    'GMAIL_FROM'          => getenv('GMAIL_FROM') ?: '❌ NOT SET',
];

$client_id     = getenv('GMAIL_CLIENT_ID');
$client_secret = getenv('GMAIL_CLIENT_SECRET');
$refresh_token = getenv('GMAIL_REFRESH_TOKEN');
$from_email    = getenv('GMAIL_FROM');

// Step 1: Get access token
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refresh_token,
        'grant_type'    => 'refresh_token',
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT    => 15,
]);
$token_response  = curl_exec($ch);
$token_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$token_curl_err  = curl_error($ch);
curl_close($ch);

$result['step1_get_token'] = [
    'http_code' => $token_http_code,
    'curl_error' => $token_curl_err ?: null,
    'response' => json_decode($token_response, true) ?: $token_response,
];

$token_data   = json_decode($token_response, true);
$access_token = $token_data['access_token'] ?? null;

if (!$access_token) {
    $result['conclusion'] = '❌ Could not get access token — see step1_get_token for error';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$result['step1_get_token']['access_token'] = '✅ Got access token';

// Step 2: Send email
$test_email = trim($_GET['email'] ?? 'godzdemonz05@gmail.com');
$message = "From: School Portal <$from_email>\r\n"
         . "To: $test_email\r\n"
         . "Subject: Gmail API Test\r\n"
         . "MIME-Version: 1.0\r\n"
         . "Content-Type: text/html; charset=UTF-8\r\n"
         . "\r\n"
         . "<h2>Gmail API is working!</h2>";

$encoded = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');

$ch2 = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['raw' => $encoded]),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 15,
]);
$send_response  = curl_exec($ch2);
$send_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$send_curl_err  = curl_error($ch2);
curl_close($ch2);

$result['step2_send_email'] = [
    'http_code'  => $send_http_code,
    'curl_error' => $send_curl_err ?: null,
    'response'   => json_decode($send_response, true) ?: $send_response,
];

$result['conclusion'] = ($send_http_code === 200 || $send_http_code === 201)
    ? "✅ Email sent to $test_email!"
    : "❌ Failed — see step2_send_email for exact error";

echo json_encode($result, JSON_PRETTY_PRINT);
