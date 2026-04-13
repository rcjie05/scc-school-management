<?php
/**
 * Resend API Mailer
 * Replaces SMTPMailer — uses Resend's HTTP API (works on Railway)
 * Requires RESEND_API_KEY environment variable set in Railway
 */

class ResendMailer {
    private $api_key;
    private $from_email;
    private $from_name;

    public function __construct() {
        $this->api_key   = getenv('RESEND_API_KEY') ?: '';
        // Always use onboarding@resend.dev — Railway blocks SMTP and Resend
        // rejects unverified domains like gmail.com as sender.
        $this->from_email = 'onboarding@resend.dev';
        $this->from_name  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'School Portal';
    }

    public function send($to_email, $to_name, $subject, $body) {
        if (empty($this->api_key)) {
            error_log('ResendMailer: RESEND_API_KEY is not set in Railway Variables.');
            return false;
        }

        $payload = json_encode([
            'from'    => $this->from_name . ' <' . $this->from_email . '>',
            'to'      => [$to_email],
            'subject' => $subject,
            'html'    => $body,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->api_key,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log('ResendMailer cURL error: ' . $curl_error);
            return false;
        }

        if ($http_code === 200 || $http_code === 201) {
            return true;
        }

        error_log('ResendMailer failed. HTTP ' . $http_code . ' — ' . $response);
        return false;
    }
}

// Drop-in alias so existing code using "new SMTPMailer()" still works
class_alias('ResendMailer', 'SMTPMailer');
