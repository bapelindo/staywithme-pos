<?php
// config/config.php
//npm install -D tailwindcss@3 postcss autoprefixer
//npm install -D @tailwindcss/forms @tailwindcss/typography
// Gunakan Environment Variables untuk Vercel, dengan fallback untuk local development
define('DB_HOST', getenv('DB_HOST') ?: '28s-br.h.filess.io'); // Atau host DB Anda
define('DB_USER', getenv('DB_USER') ?: 'staywithme_plannedam');      // User DB
define('DB_PASS', getenv('DB_PASS') ?: 'b604590cb2c93d0c2c78a0e3ae56dbb65f4c1728');          // Password DB
define('DB_NAME', getenv('DB_NAME') ?: 'staywithme_plannedam'); // Nama Database
define('DB_PORT', getenv('DB_PORT') ?: '61030');          // Port Database

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
