<?php
// ── Database configuration ────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // change for production
define('DB_PASS', '');              // change for production
define('DB_NAME', 'richelcity');
define('DB_CHARSET', 'utf8mb4');

// ── Connect ───────────────────────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // In production, log this error instead of displaying it
    error_log('DB connection failed: ' . $conn->connect_error);
    http_response_code(503);
    die('Service temporarily unavailable. Please try again later.');
}

$conn->set_charset(DB_CHARSET);

// ── App constants ─────────────────────────────────────────────────────────────
define('SITE_NAME',     'RichelCity Enterprise');
define('UPLOAD_DIR',    __DIR__ . '/assets/images/');
define('UPLOAD_URL',    'assets/images/');
define('MAX_IMG_BYTES', 4 * 1024 * 1024);    // 4 MB
define('ALLOWED_MIME',  ['image/jpeg', 'image/png', 'image/webp']);
