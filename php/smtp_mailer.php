<?php
/**
 * smtp_mailer.php — now delegates to GmailMailer
 * Uses Gmail API via OAuth2 (works on Railway — no SMTP ports needed)
 */
require_once __DIR__ . '/gmail_mailer.php';
