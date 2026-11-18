<?php

// Ensure .env is loaded if config/db.php is evaluated independently
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && empty($_ENV['DB_NAME']) && getenv('DB_NAME') === false) {
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

$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$dbPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
$dbName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'cms_tutorial');
$dbUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? (getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? 'root')));
$dbPassword = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : ($_ENV['DB_PASSWORD'] ?? '');
$dbCharset = getenv('DB_CHARSET') ?: ($_ENV['DB_CHARSET'] ?? 'utf8mb4');

$dsn = getenv('DB_DSN') ?: ($_ENV['DB_DSN'] ?? "mysql:host={$dbHost};port={$dbPort};dbname={$dbName}");

return [
    'class' => \yii\db\Connection::class,
    'dsn' => $dsn,
    'username' => $dbUser,
    'password' => $dbPassword,
    'charset' => $dbCharset,
];
