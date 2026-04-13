<?php
/**
 * smtp_mailer.php — now delegates to ResendMailer
 * Railway blocks outbound SMTP, so we use Resend's HTTP API instead.
 * Requires RESEND_API_KEY set in Railway Variables.
 */
require_once __DIR__ . '/resend_mailer.php';
