<?php
// File: app/Core/Router.php (Versi Final Bersih)
namespace App\Core;

/**
 * Class Router
 *
 * Menangani routing URL sederhana berbasis Regex.
 * Memetakan URL ke Controller dan Action (method).
 */
class Router {
    /** @var array Menyimpan semua rute yang terdaftar, dikelompokkan berdasarkan metode HTTP */
    protected $routes = [];
    /** @var array Menyimpan parameter yang diekstrak dari URL dinamis */
    protected $params = [];

    /**
     * Menambahkan definisi rute baru.
     *
     * @param string $method Metode HTTP (GET, POST, etc.). Uppercase.
     * @param string $route Pola URL (e.g., '/users/{id}/edit'). Placeholder harus {nama_variabel}.
     * @param string $controllerAction Nama Controller dan Method (e.g., 'Admin\\UserController@edit').
     * @return void
     */
    public function addRoute(string $method, string $route, string $controllerAction): void {
        // 1. Bersihkan route dari / di awal/akhir
        $route = trim($route, '/');

        // 2. Konversi placeholder {param_nama} menjadi grup Regex bernama (?P<param_nama>...)
        //    Regex [a-zA-Z0-9_-]+ cocok untuk ID/slug alfanumerik, underscore, dash.
        //    Pastikan nama placeholder hanya huruf kecil dan underscore.
        $route = preg_replace('/\{([a-z_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);

        // 3. Tambahkan anchor awal (^) dan akhir ($) serta delimiter (#) untuk Regex lengkap
        //    Kosongkan pola jika route adalah string kosong (untuk root)
        $routePattern = ($route === '') ? '#^$#' : '#^' . $route . '$#';

        // 4. Simpan route ke array $routes berdasarkan metode HTTP
        $this->routes[strtoupper($method)][$routePattern] = $controllerAction;
    }

    /**
     * Mencocokkan URL dan metode request saat ini dengan rute yang terdaftar.
     *
     * @return array|false Array ['controller', 'action', 'params'] jika cocok, false jika tidak.
     */
    protected function match(): array|false {
        // === Logika Kalkulasi URL yang Akan Dicocokkan (Sudah Diperbaiki) ===
        // 1. Dapatkan path lengkap dari URI, bersihkan / di awal/akhir
        $fullPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');

        // 2. Dapatkan base path dari BASE_URL, bersihkan / di awal/akhir
        $basePath = defined('BASE_URL') ? trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/') : '';

        // 3. Hitung path relatif terhadap base path
        $relativePath = '';
        if (!empty($basePath)) {
            if (str_starts_with($fullPath, $basePath . '/')) {
                $relativePath = substr($fullPath, strlen($basePath) + 1);
            } elseif ($fullPath === $basePath) {
                $relativePath = ''; // Akses root dari base path
            } else {
                 // Jika tidak dimulai dengan base path, anggap path lengkap sebagai relatif (mungkin untuk kasus non-standard)
                 // Jika BASE_URL sudah pasti benar, baris ini mungkin tidak perlu atau perlu penanganan berbeda
                 $relativePath = $fullPath;
            }
        } else {
            // Jika tidak ada base path (aplikasi di root domain)
            $relativePath = $fullPath;
        }

        // 4. URL yang akan dicocokkan adalah path relatif.
        //    PENTING: preg_match butuh string KOSONG ('') untuk cocok dengan pattern #^$# (root)
        $requestUrlForPreg = $relativePath;
        // === Akhir Logika Kalkulasi ===

        // 5. Dapatkan metode HTTP request
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // 6. Cari rute yang cocok berdasarkan metode HTTP
        if (isset($this->routes[$requestMethod])) {
            foreach ($this->routes[$requestMethod] as $routePattern => $controllerAction) {
                // 7. Coba cocokkan URL request (path relatif) dengan pola Regex rute
                if (preg_match($routePattern, $requestUrlForPreg, $matches)) {
                    // 8. Jika cocok, ekstrak parameter dari URL (grup bernama di $matches)
                    $params = [];
                    foreach ($matches as $key => $value) {
                        if (is_string($key)) { // Hanya ambil grup bernama
                            $params[$key] = $value;
                        }
                    }

                    // 9. Pecah string 'Controller@Action'
                    // Tambahkan penanganan jika format tidak sesuai
                    if (strpos($controllerAction, '@') === false) {
                         error_log("Invalid controller action format in routes: {$controllerAction}");
                         continue; // Lanjut ke rute berikutnya
                    }
                    list($controller, $action) = explode('@', $controllerAction, 2);

                    // 10. Kembalikan hasil pencocokan
                    return [
                        'controller' => $controller,
                        'action' => $action,
                        'params' => $params
                    ];
                }
            }
        }

        // 11. Jika tidak ada rute yang cocok
        return false;
    }

    /**
     * Menjalankan Controller dan Action yang sesuai dengan request.
     * Dipanggil dari index.php setelah rute didefinisikan.
     *
     * @return void
     * @throws \Exception Jika Controller atau Method tidak ditemukan (untuk ditangkap di index.php).
     */
    public function dispatch(): void {
        // 1. Cocokkan URL
        $match = $this->match();

        // 2. Handle jika tidak ada rute cocok (404 Not Found)
        if ($match === false) {
            http_response_code(404);
            $viewPath = '../app/Views/public/errors/404.php'; // Path ke view 404
            $layoutPath = '../app/Views/layouts/public_layout.php'; // Path ke layout publik

             if(file_exists($viewPath) && file_exists($layoutPath)) {
                 // Siapkan data untuk view dan layout
                 $data = ['message' => "URL yang diminta tidak ditemukan.", 'pageTitle' => "404 - Tidak Ditemukan"];
                 extract($data); // Ekstrak variabel $message dan $pageTitle

                 // Muat layout, yang akan memuat $viewPath di dalamnya
                 require_once $layoutPath;

             } else {
                 // Fallback jika view 404 atau layout tidak ada
                 if (!file_exists($layoutPath)) error_log("Layout file not found: " . $layoutPath);
                 if (!file_exists($viewPath)) error_log("404 View file not found: " . $viewPath);

                 header('Content-Type: text/plain; charset=utf-8');
                 echo "404 Not Found - Resource or required layout file missing.";
             }
            exit;
        }
        // 3. Bangun nama kelas Controller lengkap
        $controllerClassName = 'App\\Controllers\\' . $match['controller'];
        $actionName = $match['action'];
        $params = $match['params'];

        // 4. Periksa apakah kelas Controller ada (menggunakan autoloader)
        if (!class_exists($controllerClassName)) {
            throw new \Exception("Controller class '{$controllerClassName}' not found.");
        }

        // 5. Buat instance dari Controller
        $controllerInstance = new $controllerClassName();

        // 6. Periksa apakah method (action) ada di dalam Controller
        if (!method_exists($controllerInstance, $actionName)) {
            throw new \Exception("Action method '{$actionName}' not found in controller '{$controllerClassName}'.");
        }

        // 7. Panggil method Controller dengan parameter dari URL
        try {
            call_user_func_array([$controllerInstance, $actionName], $params);
        } catch (\Throwable $e) {
             // Tangkap error/exception dari eksekusi Action Controller
             error_log("Error executing action {$controllerClassName}@{$actionName}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
             // Lempar ulang agar ditangani oleh try-catch di index.php
             throw $e;
        }
    }

} // Akhir kelas Router
?>