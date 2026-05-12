<?php
// config/config.php
//npm install -D tailwindcss@3 postcss autoprefixer
//npm install -D @tailwindcss/forms @tailwindcss/typography
// Gunakan Environment Variables untuk Vercel, dengan fallback untuk local development
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com'); // Atau host DB Anda
define('DB_USER', getenv('DB_USER') ?: '4TnpUUxik5ZLHTT.root');      // User DB
define('DB_PASS', getenv('DB_PASS') ?: 'hweuQGiW36RtoJLw');          // Password DB
define('DB_NAME', getenv('DB_NAME') ?: 'staywithme_db'); // Nama Database
define('DB_PORT', getenv('DB_PORT') ?: '4000');          // Port Database
define('DB_SSL_CA', __DIR__ . '/isrgrootx1.pem');       // Path to SSL CA Certificate


// URL dasar aplikasi, dinamis untuk Vercel dan lokal
if (getenv('APP_URL')) {
    define('BASE_URL', getenv('APP_URL'));
} elseif (getenv('VERCEL_URL')) {
    // Vercel Preview/Production URL (usually without protocol)
    define('BASE_URL', 'https://' . getenv('VERCEL_URL'));
} elseif (isset($_SERVER['HTTP_HOST'])) {
    // Auto-detect dari HTTP request (untuk production dan local)
    // Deteksi protocol dengan cara yang lebih kompatibel
    // Cek X-Forwarded-Proto untuk reverse proxy/load balancer (Cloudflare, nginx, dll)
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST']; // 'bacelor.bapel.my.id' atau 'localhost'

    // Jika menggunakan localhost, tambahkan path aplikasi
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        define('BASE_URL', $protocol . '://' . $host . '/staywithme-pos');
    } else {
        // Untuk domain production, gunakan root domain
        define('BASE_URL', $protocol . '://' . $host);
    }
} else {
    // Fallback untuk pengembangan lokal (CLI atau environment tanpa HTTP)
    define('BASE_URL', 'http://localhost/staywithme-pos');
}
define('APP_NAME', 'Bacelor Cafe');
define('CAFE_ADDRESS', 'Jl. Hayam Wuruk I No.12, Krajan, Putat Kidul, Kec. Gondanglegi, Kabupaten Malang, Jawa Timur 65174');
define('CAFE_PHONE', '0822-2911-4960');
define('ENVIRONMENT', 'development'); // Atau 'production' untuk live
// Pengaturan lain (misal: kunci API, path, dll.)
?>