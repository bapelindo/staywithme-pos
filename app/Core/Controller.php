<?php
// File: app/Core/Controller.php
namespace App\Core;

/**
 * Class Controller
 *
 * Base Controller untuk semua controller aplikasi.
 * Menyediakan metode helper untuk memuat view, model, redirect, dll.
 */
class Controller {

    /**
     * Memuat view beserta layoutnya (jika ditentukan).
     *
     * @param string $viewName Nama view (e.g., 'public.home', 'admin.dashboard').
     * @param array $data Data yang akan diekstrak menjadi variabel di view dan layout.
     * @param string|null $layout Nama file layout di 'app/Views/layouts/' (e.g., 'public_layout', 'admin_layout'). Default 'public_layout'. Jika null, hanya view yang dimuat.
     * @return void
     */
    protected function view(string $viewName, array $data = [], ?string $layout = 'public_layout'): void {
        // 1. Ekstrak data agar bisa diakses sebagai variabel ($pageTitle, $orders, dll.)
        extract($data);

        // 2. Bangun path ke file view spesifik.
        $viewPath = '../app/Views/' . str_replace('.', '/', $viewName) . '.php';

        // 3. Periksa apakah file view spesifik ada.
        if (!file_exists($viewPath)) {
            error_log("View file not found: " . $viewPath . " requested by " . get_class($this));
            // Idealnya tampilkan view 404 yang proper
            http_response_code(404);
            // Coba muat view error 404 jika ada
            $errorViewPath = '../app/Views/public/errors/404.php'; // atau path lain
            if(file_exists($errorViewPath)) {
                 // Kirim pesan ke view error
                 $message = "View file '{$viewName}' tidak ditemukan.";
                 require_once $errorViewPath;
            } else {
                die("Error 404: View file not found."); // Fallback
            }
            exit; // Hentikan eksekusi
        }

        // 4. Muat layout atau view langsung
        if ($layout !== null) {
            $layoutPath = '../app/Views/layouts/' . $layout . '.php';

            if (!file_exists($layoutPath)) {
                error_log("Layout file not found: " . $layoutPath . " requested by " . get_class($this));
                die("Error: Layout file '{$layout}.php' not found."); // Error fatal jika layout utama tidak ada
            }
            // Muat layout. Variabel $viewPath akan digunakan di dalam layout
            // untuk include konten view spesifik.
            require_once $layoutPath;
        } else {
            // Langsung muat file view spesifik jika tidak ada layout.
            require_once $viewPath;
        }
    }

    /**
     * Memuat (instantiate) sebuah model.
     * Mengandalkan Composer Autoloader.
     *
     * @param string $modelName Nama kelas Model (e.g., 'User', 'Order').
     * @return object|null Instance dari Model atau null jika gagal.
     */
    protected function model(string $modelName): ?object {
        // Buat nama kelas lengkap dengan namespace
        $modelClass = 'App\\Models\\' . $modelName;
        try {
            // Cek apakah kelas ada (menggunakan autoloader)
            if (class_exists($modelClass)) {
                // Buat instance baru dari model
                return new $modelClass();
            } else {
                 throw new \Exception("Model class '{$modelClass}' not found.");
            }
        } catch (\Throwable $e) {
            // Tangani error jika model tidak ditemukan atau ada masalah lain
            error_log("Error loading model '{$modelName}': " . $e->getMessage());
            // Bisa tampilkan error atau return null/false
            die("Error: Gagal memuat model '{$modelName}'.");
            // return null;
        }
    }

    /**
     * Melakukan redirect ke URL internal aplikasi.
     * Menggunakan BASE_URL dari config.php.
     *
     * @param string $url Path tujuan (e.g., '/admin/login', 'orders/show/1').
     * @return void
     */
    protected function redirect(string $url): void {
        // Pastikan BASE_URL didefinisikan
        if (!defined('BASE_URL')) {
            die('Error: BASE_URL constant is not defined.');
        }
        $location = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        header('Location: ' . $location);
        exit; // Hentikan eksekusi script setelah redirect
    }

    /**
     * Mengirim response dalam format JSON.
     * Berguna untuk API atau request AJAX.
     *
     * @param mixed $data Data yang akan di-encode ke JSON.
     * @param int $statusCode Kode status HTTP (default 200 OK).
     * @return void
     */
    protected function jsonResponse(mixed $data, int $statusCode = 200): void {
        // Hentikan output buffering jika ada
        if (ob_get_level()) {
            ob_end_clean();
        }
        // Set header Content-Type ke application/json
        header('Content-Type: application/json; charset=utf-8');
        // Set kode status HTTP
        http_response_code($statusCode);
        // Encode data ke JSON dan output
        echo json_encode($data);
        // Hentikan eksekusi script
        exit;
    }
}
?>