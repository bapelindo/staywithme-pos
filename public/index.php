<?php
/**
 * Stay With Me Cafe - POS QR Ordering System
 *
 * Entry Point Aplikasi (Front Controller).
 * Semua request publik masuk melalui file ini.
 */

// Definisikan Root Path untuk path absolut yang andal di seluruh aplikasi
// dirname(__DIR__) akan menunjuk ke direktori parent dari direktori saat ini (yaitu, root proyek)
define('ROOT_PATH', dirname(__DIR__));

// 1. Mulai Session
// Harus dipanggil sebelum output apapun ke browser.
// Pastikan session handling dikonfigurasi dengan benar di php.ini (terutama save path).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Pengaturan Error Reporting (Untuk Development)
// Selama pengembangan, tampilkan semua error.
// Untuk produksi, set ke 0 dan gunakan logging.
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Set ke '0' di produksi

// 3. Muat Konfigurasi Aplikasi
// Berisi konstanta seperti kredensial DB, BASE_URL, dll.
require_once __DIR__ . '/../config/config.php';

// --- START COMPREHENSIVE DEBUG ---
echo "<div style='background: #ffc; padding: 1em; border: 2px solid #f00; margin: 1em; font-family: sans-serif; z-index: 9999; position: relative;'>";
echo "<h2>DEBUGGING ENVIRONMENT</h2>";
echo "<p><strong>Vercel Environment (VERCEL_ENV):</strong> " . (getenv('VERCEL_ENV') ?: 'Not Set') . "</p>";
echo "<strong><u>Database Variables:</u></strong><br>";
echo "<strong>DB_HOST:</strong> " . (getenv('DB_HOST') ?: '<em>Not Set</em>') . "<br>";
echo "<strong>DB_USER:</strong> " . (getenv('DB_USER') ?: '<em>Not Set</em>') . "<br>";
echo "<strong>DB_PASS:</strong> " . (getenv('DB_PASS') ? '<em>Set (hidden)</em>' : '<em>Not Set</em>') . "<br>";
echo "<strong>DB_NAME:</strong> " . (getenv('DB_NAME') ?: '<em>Not Set</em>') . "<br>";
echo "<strong>DB_PORT:</strong> " . (getenv('DB_PORT') ?: '<em>Not Set</em>') . "<br><br>";
echo "<strong><u>URL Variables:</u></strong><br>";
echo "<strong>APP_URL:</strong> " . (getenv('APP_URL') ?: '<em>Not Set</em>') . "<br>";
echo "<strong>VERCEL_URL (fallback):</strong> " . (getenv('VERCEL_URL') ?: '<em>Not Set</em>') . "<br><br>";
echo "<strong><u>Final BASE_URL being used:</u></strong><br>";
echo "<strong>BASE_URL:</strong> " . (defined('BASE_URL') ? BASE_URL : '<em>Not Defined</em>') . "<br>";
echo "</div>";
// --- END COMPREHENSIVE DEBUG ---



// 4. Muat Autoloader Composer
// Ini akan otomatis memuat semua kelas dari direktori 'app/' (sesuai PSR-4)
// dan library dari 'vendor/' saat dibutuhkan.
require_once __DIR__ . '/../vendor/autoload.php';

// 5. Inisialisasi Router
// Gunakan namespace yang benar sesuai struktur folder.
$router = new App\Core\Router();

// 6. Muat Definisi Rute (Routes)
// File ini akan memanggil $router->addRoute() untuk semua rute aplikasi.
require_once __DIR__ . '/../app/routes.php';

// 7. Dispatch Router
// Router akan mencocokkan URL saat ini dengan rute yang terdaftar
// dan mengeksekusi method Controller yang sesuai.
try {
    $router->dispatch();
} catch (\Throwable $e) {
    // Tangkap error/exception yang mungkin tidak tertangani di Controller/Router
    // Ini adalah fallback error handler sederhana.
    error_log("Unhandled Exception/Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Tampilkan pesan error generic ke pengguna (jangan tampilkan detail error di produksi)
    http_response_code(500); // Internal Server Error

    // Idealnya, tampilkan view error 500 yang user-friendly
    // Contoh sederhana:
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>Terjadi Kesalahan</h1>";
    echo "<p>Maaf, terjadi kesalahan pada server. Silakan coba lagi nanti.</p>";
    // HANYA tampilkan detail error jika BUKAN di lingkungan produksi
    // Misalnya, cek konstanta lingkungan (e.g., define('ENVIRONMENT', 'development'); di config.php)
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<pre>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</pre>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES) . "</pre>";
    }
    echo "</body></html>";
    exit;
}

?>