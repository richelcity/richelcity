<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'richelcity');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<p style="font-family:sans-serif;padding:2rem;color:red;">
        <strong>DB connection failed:</strong> ' . htmlspecialchars($conn->connect_error) . '<br><br>
        Fix: Laragon must be running, database <b>richelcity</b> must exist,
        and DB_USER/DB_PASS in admin/config.php must be correct.
    </p>');
}
$conn->set_charset('utf8mb4');

define('UPLOAD_DIR',  dirname(__DIR__) . '/assets/images/');
define('UPLOAD_URL',  '../assets/images/');
define('MAX_IMG_SIZE', 4 * 1024 * 1024);
define('ALLOWED_IMG',  ['image/jpeg', 'image/png', 'image/webp']);
