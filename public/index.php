<?php
/**
 * Stay With Me Cafe - POS QR Ordering System
 *
 * Entry Point Aplikasi (Front Controller).
 * Semua request publik masuk melalui file ini.
 */

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
require_once '../config/config.php';

// 4. Muat Autoloader Composer
// Ini akan otomatis memuat semua kelas dari direktori 'app/' (sesuai PSR-4)
// dan library dari 'vendor/' saat dibutuhkan.
require_once '../vendor/autoload.php';

// 5. Inisialisasi Router
// Gunakan namespace yang benar sesuai struktur folder.
$router = new App\Core\Router();

// 6. Muat Definisi Rute (Routes)
// File ini akan memanggil $router->addRoute() untuk semua rute aplikasi.
require_once '../app/routes.php';

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
    // if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    //     echo "<pre>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "</pre>";
    //     echo "<pre>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES) . "</pre>";
    // }
    echo "</body></html>";
    exit;
}

?>