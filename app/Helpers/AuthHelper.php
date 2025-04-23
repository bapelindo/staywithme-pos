<?php
namespace App\Helpers;

/**
 * Class AuthHelper
 *
 * Mengelola otentikasi dan otorisasi pengguna berbasis Session.
 */
class AuthHelper {

    /**
     * Memulai session jika belum dimulai.
     * Sebaiknya dipanggil sekali di awal script (misal di index.php).
     */
    public static function ensureSessionStarted(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Memeriksa apakah ada pengguna yang sedang login.
     *
     * @return bool True jika login, false jika tidak.
     */
    public static function isLoggedIn(): bool {
        self::ensureSessionStarted();
        return isset($_SESSION['user_id']);
    }

    /**
     * Menyimpan data pengguna ke session setelah login berhasil.
     *
     * @param array $user Data pengguna dari database (minimal harus ada 'id', 'name', 'role').
     */
    public static function loginUser(array $user): void {
        self::ensureSessionStarted();
        // Regenerate session ID untuk mencegah session fixation attacks
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = (string)$user['name'];
        $_SESSION['user_role'] = (string)$user['role']; // 'admin', 'staff', 'kitchen'
    }

    /**
     * Menghapus data pengguna dari session (logout).
     */
    public static function logoutUser(): void {
        self::ensureSessionStarted();
        session_unset(); // Hapus semua variabel session
        session_destroy(); // Hancurkan session
        // Opsional: Hapus cookie session jika digunakan
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    /**
     * Mendapatkan ID pengguna yang sedang login.
     *
     * @return int|null ID pengguna atau null jika tidak login.
     */
    public static function getUserId(): ?int {
        self::ensureSessionStarted();
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Mendapatkan nama pengguna yang sedang login.
     *
     * @return string|null Nama pengguna atau null jika tidak login.
     */
    public static function getUserName(): ?string {
        self::ensureSessionStarted();
        return $_SESSION['user_name'] ?? null;
    }

    /**
     * Mendapatkan peran (role) pengguna yang sedang login.
     *
     * @return string|null Role pengguna ('admin', 'staff', 'kitchen') atau null.
     */
    public static function getUserRole(): ?string {
        self::ensureSessionStarted();
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Memeriksa apakah pengguna yang login memiliki setidaknya salah satu peran yang diizinkan.
     * Jika tidak, akan menghentikan eksekusi script.
     * Berguna di awal method Controller untuk proteksi halaman.
     *
     * @param array $allowedRoles Array berisi string peran yang diizinkan (e.g., ['admin', 'staff']).
     * @param string|null $redirectUrl URL untuk redirect jika akses ditolak (default: null, hanya tampilkan error).
     */
    public static function requireRole(array $allowedRoles, ?string $redirectUrl = '/admin/login?error=forbidden'): void {
        self::ensureSessionStarted();
        if (!self::isLoggedIn()) {
            if ($redirectUrl) {
                UrlHelper::redirect($redirectUrl);
            } else {
                http_response_code(401); // Unauthorized
                die('Unauthorized: Login required.');
            }
        }

        $userRole = self::getUserRole();
        if ($userRole === null || !in_array($userRole, $allowedRoles)) {
             if ($redirectUrl) {
                UrlHelper::redirect($redirectUrl);
            } else {
                http_response_code(403); // Forbidden
                die('Forbidden: Insufficient privileges.');
            }
        }
    }

    /**
     * Memeriksa apakah pengguna adalah Admin. Shortcut untuk requireRole(['admin']).
     *
     * @param string|null $redirectUrl URL untuk redirect jika bukan admin.
     */
    public static function requireAdmin(?string $redirectUrl = '/admin/login?error=forbidden'): void {
        self::requireRole(['admin'], $redirectUrl);
    }
}
?>