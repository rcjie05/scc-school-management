<?php
/**
 * Resend Debug Tool — DELETE after fixing
 * Visit: https://your-railway-url/php/api/test_otp.php?email=you@gmail.com
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../config.php';

$result = [];
$result['RESEND_API_KEY'] = getenv('RESEND_API_KEY') ? '✅ SET (' . strlen(getenv('RESEND_API_KEY')) . ' chars)' : '❌ NOT SET';

$test_email = trim($_GET['email'] ?? '');
if (!$test_email) {
    $result['instruction'] = 'Add ?email=youremail@gmail.com to the URL';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

$api_key = getenv('RESEND_API_KEY');
$payload = json_encode([
    'from'    => 'School Portal <onboarding@resend.dev>',
    'to'      => [$test_email],
    'subject' => 'OTP Test',
    'html'    => '<p>Your OTP is <strong>123456</strong></p>',
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
    CURLOPT_VERBOSE => false,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

$result['http_code']       = $http_code;
$result['resend_response'] = json_decode($response, true) ?: $response;
$result['curl_error']      = $curl_err ?: null;
$result['result']          = ($http_code === 200 || $http_code === 201) ? '✅ Sent!' : '❌ Failed';

echo json_encode($result, JSON_PRETTY_PRINT);
