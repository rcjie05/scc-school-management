<?php
/**
 * Secure Session Manager - Simplified & Reliable
 */

function configureSecureSession() {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
             || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.use_strict_mode',  1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid',    0);
    ini_set('session.cookie_httponly',  1);
    ini_set('session.cookie_samesite',  'Lax');
    ini_set('session.cookie_secure',    $isHttps ? 1 : 0);
    ini_set('session.gc_maxlifetime',   1200); // 20 minutes
    ini_set('session.cookie_lifetime',  0);
    ini_set('session.name',             'SCC_SESS');
}

function startSecureSession() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    configureSecureSession();
    session_start();
}

function validateSession() {
    if (!isset($_SESSION['user_id'])) return false;

    // Only check inactivity timeout - nothing else
    $timeout = 1200; // 20 minutes
    if (isset($_SESSION['last_activity'])) {
        if ((time() - $_SESSION['last_activity']) > $timeout) {
            destroySession();
            return false;
        }
    }

    // Update last activity on every request
    $_SESSION['last_activity'] = time();
    return true;
}

function initializeSession($user) {
    session_regenerate_id(true);
    $_SESSION['user_id']       = $user['id'];
    $_SESSION['name']          = $user['name'];
    $_SESSION['email']         = $user['email'];
    $_SESSION['role']          = $user['role'];
    $_SESSION['course']        = $user['course'] ?? '';
    $_SESSION['last_activity'] = time();
    $_SESSION['created_at']    = time();
}

function destroySession() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function checkLoginRateLimit($email) {
    $key      = 'login_attempts_' . md5($email . ($_SERVER['REMOTE_ADDR'] ?? ''));
    $maxTries = 5;
    $window   = 900; // 15 minutes

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
    }

    $data = &$_SESSION[$key];
    if ((time() - $data['first_attempt']) > $window) {
        $data = ['count' => 0, 'first_attempt' => time()];
    }

    $data['count']++;

    if ($data['count'] > $maxTries) {
        $remaining = $window - (time() - $data['first_attempt']);
        return [
            'allowed'  => false,
            'message'  => "Too many failed attempts. Try again in " . ceil($remaining / 60) . " minute(s)."
        ];
    }
    return ['allowed' => true];
}

function clearLoginRateLimit($email) {
    $key = 'login_attempts_' . md5($email . ($_SERVER['REMOTE_ADDR'] ?? ''));
    unset($_SESSION[$key]);
}
