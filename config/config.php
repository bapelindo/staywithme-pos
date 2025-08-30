<?php
// config/config.php
//npm install -D tailwindcss@3 postcss autoprefixer
//npm install -D @tailwindcss/forms @tailwindcss/typography
// Gunakan Environment Variables untuk Vercel, dengan fallback untuk local development
define('DB_HOST', getenv('DB_HOST') ?: 'e09yxd.h.filess.io'); // Atau host DB Anda
define('DB_USER', getenv('DB_USER') ?: 'staywithme_scientist');      // User DB
define('DB_PASS', getenv('DB_PASS') ?: 'd0e40616a226241f4852a1868b43e75d66ccc79b');          // Password DB
define('DB_NAME', getenv('DB_NAME') ?: 'staywithme_scientist'); // Nama Database
define('DB_PORT', getenv('DB_PORT') ?: '61000');          // Port Database

// URL dasar aplikasi, dinamis untuk Vercel dan lokal
if (getenv('APP_URL')) {
    define('BASE_URL', getenv('APP_URL'));
} else {
    // Fallback untuk pengembangan lokal
    define('BASE_URL', 'https://staywithme-pos.vercel.app');
}
define('APP_NAME', 'Stay With Me');
define('CAFE_ADDRESS', 'Jl. Hayam Wuruk I No.12, Krajan, Putat Kidul, Kec. Gondanglegi, Kabupaten Malang, Jawa Timur 65174');
define('CAFE_PHONE', '0822-2911-4960');
define('ENVIRONMENT', 'development'); // Atau 'production' untuk live
// Pengaturan lain (misal: kunci API, path, dll.)
?>