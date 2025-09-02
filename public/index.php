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

// 3. Muat Konfigurasi Aplikasi
// Berisi konstanta seperti kredensial DB, BASE_URL, dll.
require_once __DIR__ . '/../config/config.php';

// 1. Mulai Session
// Harus dipanggil sebelum output apapun ke browser.
// Untuk Vercel, gunakan DatabaseSessionHandler untuk persistensi sesi.
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/DatabaseSessionHandler.php';
use App\Core\DatabaseSessionHandler;

if (session_status() === PHP_SESSION_NONE) {
    $handler = new DatabaseSessionHandler();
    session_set_save_handler($handler, true);
    session_start();
}

// 4. Muat Autoloader Composer



// 4. Muat Autoloader Composer
// Ini akan otomatis memuat semua kelas dari direktori 'app/' (sesuai PSR-4)
// dan library dari 'vendor/' saat dibutuhkan.
require_once __DIR__ . '/../vendor/autoload.php';

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