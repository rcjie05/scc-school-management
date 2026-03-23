<?php
/**
 * Lightweight SMTP Mailer
 * Handles Gmail / Yahoo / Outlook SMTP sending without PHPMailer dependency
 * Configure settings in php/config.php or directly here
 */

class SMTPMailer {
    private $host;
    private $port;
    private $username;
    private $password;
    private $encryption; // 'tls' or 'ssl'
    private $from_email;
    private $from_name;
    private $socket;
    private $last_response;

    public function __construct($config = []) {
        $this->host       = $config['host']       ?? SMTP_HOST;
        $this->port       = $config['port']       ?? SMTP_PORT;
        $this->username   = $config['username']   ?? SMTP_USERNAME;
        $this->password   = $config['password']   ?? SMTP_PASSWORD;
        $this->encryption = $config['encryption'] ?? SMTP_ENCRYPTION;
        $this->from_email = $config['from_email'] ?? SMTP_FROM_EMAIL;
        $this->from_name  = $config['from_name']  ?? SMTP_FROM_NAME;
    }

    public function send($to_email, $to_name, $subject, $body) {
        try {
            $this->connect();
            $this->authenticate();
            $this->sendMail($to_email, $to_name, $subject, $body);
            $this->disconnect();
            return true;
        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            $this->disconnect();
            return false;
        }
    }

    private function connect() {
        $host = $this->host;

        if ($this->encryption === 'ssl') {
            $host = 'ssl://' . $host;
        }

        $this->socket = @fsockopen($host, $this->port, $errno, $errstr, 15);
        if (!$this->socket) {
            throw new Exception("Cannot connect to SMTP: $errstr ($errno)");
        }

        $this->read(); // Read greeting

        if ($this->encryption === 'tls') {
            $this->cmd("EHLO localhost");
            $this->cmd("STARTTLS");
            stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        $this->cmd("EHLO localhost");
    }

    private function authenticate() {
        $this->cmd("AUTH LOGIN");
        $this->cmd(base64_encode($this->username));
        $this->cmd(base64_encode($this->password));
    }

    private function sendMail($to_email, $to_name, $subject, $body) {
        $this->cmd("MAIL FROM: <{$this->from_email}>");
        $this->cmd("RCPT TO: <{$to_email}>");
        $this->cmd("DATA");

        $headers  = "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "To: {$to_name} <{$to_email}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "X-Mailer: SCC-School-System\r\n";
        $headers .= "\r\n";

        $this->write($headers . $body . "\r\n.");
        $this->read();
    }

    private function disconnect() {
        if ($this->socket) {
            @$this->cmd("QUIT");
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    private function cmd($command) {
        $this->write($command);
        return $this->read();
    }

    private function write($data) {
        fwrite($this->socket, $data . "\r\n");
    }

    private function read() {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        $this->last_response = $response;
        $code = intval(substr($response, 0, 3));
        if ($code >= 400) {
            throw new Exception("SMTP Error $code: $response");
        }
        return $response;
    }

    public function getLastResponse() {
        return $this->last_response;
    }
}
