<?php
namespace App\Helpers;

/**
 * Class UrlHelper
 *
 * Menyediakan fungsi untuk membuat URL absolut dan melakukan redirect.
 */
class UrlHelper {

    /**
     * Menghasilkan URL absolut berdasarkan BASE_URL.
     *
     * @param string $path Path relatif (e.g., '/admin/users').
     * @return string URL absolut.
     */
    public static function baseUrl(string $path = ''): string {
        // Pastikan BASE_URL didefinisikan dan tidak ada / ganda
        $baseUrl = rtrim(BASE_URL, '/');
        $path = ltrim($path, '/');
        return $baseUrl . '/' . $path;
    }

    /**
     * Menghasilkan URL absolut ke file aset (CSS, JS, Images) di folder public/assets.
     *
     * @param string $path Path relatif di dalam folder assets (e.g., 'css/style.css').
     * @return string URL absolut ke aset.
     */
    public static function asset(string $path): string {
        return self::baseUrl('assets/' . ltrim($path, '/'));
    }

    /**
     * Melakukan redirect ke URL internal aplikasi.
     *
     * @param string $path Path relatif tujuan redirect.
     */
    public static function redirect(string $path): void {
        header('Location: ' . self::baseUrl($path));
        exit; // Penting untuk menghentikan eksekusi script setelah redirect
    }

    /**
     * Melakukan redirect ke URL eksternal.
     *
     * @param string $url URL absolut tujuan redirect.
     */
    public static function redirectExternal(string $url): void {
        // Validasi sederhana untuk memastikan itu URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            header('Location: ' . $url);
            exit;
        } else {
            // Handle error jika URL tidak valid
            // Mungkin redirect ke halaman error internal atau log error
            die("Invalid external URL for redirect.");
        }
    }
}
?>