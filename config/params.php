<?php

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            putenv("$key=$val");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

return [
    'companyName' => getenv('COMPANY_NAME') ?: 'SARGNEXUS CMS',
    'companyLogo' => getenv('COMPANY_LOGO') ?: '/uploads/logo.png',
    'adminEmail' => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
    'senderEmail' => getenv('SENDER_EMAIL') ?: 'noreply@example.com',
    'senderName' => getenv('SENDER_NAME') ?: 'SARGNEXUS CMS',
    'enableLoginOtp' => filter_var(getenv('ENABLE_LOGIN_OTP') ?: false, FILTER_VALIDATE_BOOLEAN),
    'smtpHost' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'smtpPort' => getenv('SMTP_PORT') ?: 587,
    'smtpUser' => getenv('SMTP_USER') ?: '',
    'smtpPass' => getenv('SMTP_PASS') ?: '',
];
