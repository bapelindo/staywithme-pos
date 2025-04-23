<?php
// config/config.php
define('APP_ENV', 'development');

// Base URL (sesuaikan dengan setup Anda)
define('BASE_URL', 'http://localhost/staywithme'); // Ganti jika perlu

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', 'root');     // Ganti dengan password database Anda
define('DB_NAME', 'stay_with_me_db'); // Nama database Anda

// App Name
define('APP_NAME', 'Stay With Me Cafe');


// Direktori Aplikasi

define('APPROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app');
define('URLROOT', BASE_URL);
define('PUBLICROOT', URLROOT . '/public');

// Lain-lain (misal: kunci API, pengaturan default)
// ...
?>