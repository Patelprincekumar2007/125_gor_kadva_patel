<?php

declare(strict_types=1);

// Enable global output buffering to prevent header issues during redirection
if (session_status() === PHP_SESSION_NONE) {
    ob_start();
}


$isLocal = false;
if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    $isLocal = true;
} else if (PHP_SAPI === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    $isLocal = true;
}

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'matrimony_125_gor');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('SITE_URL', 'http://localhost/125 gor');
} else {
    define('DB_HOST', 'sql107.infinityfree.com');
    define('DB_NAME', 'if0_41938080_125_Gor_Kadva_patel');
    define('DB_USER', 'if0_41938080');
    define('DB_PASS', 'Patelprince2007');
    define('SITE_URL', 'https://125gorkadvapatel.infinityfreeapp.com');
}
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME', '125 Gor Kadva Patel Samaj Matrimony');

// File Upload Configuration
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('PROFILE_UPLOAD_PATH', UPLOAD_DIR . 'profiles/');
define('GALLERY_UPLOAD_PATH', UPLOAD_DIR . 'gallery/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);


try {

    $dsn = "mysql:host=" . DB_HOST .
           ";dbname=" . DB_NAME .
           ";charset=" . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [

        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false

    ]);

} catch (PDOException $e) {

    error_log(
        date('Y-m-d H:i:s') .
        " | Database Error: " .
        $e->getMessage() .
        PHP_EOL,
        3,
        __DIR__ . '/db_error_log.txt'
    );

    die('Database temporarily unavailable.');
}
