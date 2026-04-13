<?php
/**
 * Gmail API Mailer
 * Uses OAuth2 + Gmail API to send emails — works on Railway (HTTP, not SMTP)
 * Required Railway Variables:
 *   GMAIL_CLIENT_ID, GMAIL_CLIENT_SECRET, GMAIL_REFRESH_TOKEN, GMAIL_FROM
 */

class GmailMailer {
    private $client_id;
    private $client_secret;
    private $refresh_token;
    private $from_email;
    private $from_name;

    public function __construct() {
        $this->client_id     = getenv('GMAIL_CLIENT_ID')     ?: '';
        $this->client_secret = getenv('GMAIL_CLIENT_SECRET') ?: '';
        $this->refresh_token = getenv('GMAIL_REFRESH_TOKEN') ?: '';
        $this->from_email    = getenv('GMAIL_FROM')          ?: getenv('SMTP_USERNAME') ?: '';
        $this->from_name     = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'School Portal';
    }

    private function getAccessToken() {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'client_id'     => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token,
                'grant_type'    => 'refresh_token',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT    => 15,
        ]);
        $response  = curl_exec($ch);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            error_log('GmailMailer: token curl error: ' . $curl_err);
            return null;
        }

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            error_log('GmailMailer: failed to get access token: ' . $response);
            return null;
        }
        return $data['access_token'];
    }

    public function send($to_email, $to_name, $subject, $body) {
        if (empty($this->client_id) || empty($this->client_secret) || empty($this->refresh_token)) {
            error_log('GmailMailer: Missing GMAIL_CLIENT_ID, GMAIL_CLIENT_SECRET or GMAIL_REFRESH_TOKEN');
            return false;
        }

        $access_token = $this->getAccessToken();
        if (!$access_token) return false;

        // Build RFC 2822 email
        $from    = $this->from_name . ' <' . $this->from_email . '>';
        $to      = $to_name . ' <' . $to_email . '>';
        $message = "From: $from\r\n"
                 . "To: $to\r\n"
                 . "Subject: $subject\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . "Content-Type: text/html; charset=UTF-8\r\n"
                 . "\r\n"
                 . $body;

        $encoded = rtrim(strtr(base64_encode($message), '+/', '-_'), '=');

        $payload = json_encode(['raw' => $encoded]);

        $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($curl_err) {
            error_log('GmailMailer: send curl error: ' . $curl_err);
            return false;
        }

        if ($http_code === 200 || $http_code === 201) {
            return true;
        }

        error_log('GmailMailer: send failed HTTP ' . $http_code . ': ' . $response);
        return false;
    }
}

// Drop-in alias so all existing code using "new SMTPMailer()" still works
class_alias('GmailMailer', 'SMTPMailer');
