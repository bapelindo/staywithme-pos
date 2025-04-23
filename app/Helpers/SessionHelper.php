<?php
// File: app/Helpers/SessionHelper.php (Versi Final Bersih - Overlap + AutoHide)
namespace App\Helpers;

use App\Helpers\SanitizeHelper;
use App\Helpers\AuthHelper;

class SessionHelper {

    private const FLASH_KEY = '_flash_messages';
    private const FLASH_DATA_KEY = '_flash_data';

    private static function ensureSessionStarted(): void {
        AuthHelper::ensureSessionStarted();
    }

    // --- Pesan Flash (String) ---
    public static function setFlash(string $key, string $message): void {
        self::ensureSessionStarted();
        $_SESSION[self::FLASH_KEY][$key] = $message;
    }
    public static function getFlash(string $key): ?string {
        self::ensureSessionStarted();
        $message = $_SESSION[self::FLASH_KEY][$key] ?? null;
        if ($message !== null) {
            unset($_SESSION[self::FLASH_KEY][$key]);
            if (empty($_SESSION[self::FLASH_KEY])) { unset($_SESSION[self::FLASH_KEY]); }
        }
        return $message;
    }
    public static function hasFlash(string $key): bool { /*...*/ self::ensureSessionStarted(); return isset($_SESSION[self::FLASH_KEY][$key]); }

    // --- Data Flash (Mixed Type) ---
    public static function setFlashData(string $key, mixed $value): void {
        self::ensureSessionStarted();
        $_SESSION[self::FLASH_DATA_KEY][$key] = $value;
    }
    public static function getFlashData(string $key): mixed {
        self::ensureSessionStarted();
        $value = $_SESSION[self::FLASH_DATA_KEY][$key] ?? null;
        if ($value !== null) {
            unset($_SESSION[self::FLASH_DATA_KEY][$key]);
            if (empty($_SESSION[self::FLASH_DATA_KEY])) { unset($_SESSION[self::FLASH_DATA_KEY]); }
        }
        return $value;
    }
    public static function hasFlashData(string $key): bool { /*...*/ self::ensureSessionStarted(); return isset($_SESSION[self::FLASH_DATA_KEY][$key]); }

    
    // --- Metode untuk Menampilkan Flash Message (String) dengan Auto-Hide JS & CSS Manual ---

    /**
     * >>> PERUBAHAN DI SINI <<<
     * Menampilkan pesan flash string di dalam wrapper untuk CSS manual overlap.
     * Kelas dasar sekarang hanya untuk styling internal div.
     *
     * @param string $key Kunci pesan ('success', 'error', 'warning', 'info').
     * @param string $cssClassBase Kelas CSS dasar HANYA untuk styling BUKAN posisi.
     * @param array $cssClassMap Mapping key ke kelas CSS warna/styling spesifik.
     * @return void Echoes HTML & JS output.
     */
    public static function displayFlash(
        string $key,
        // Default class dasar TANPA posisi fixed
        string $cssClassBase = 'p-4 rounded-md border text-sm',
        array $cssClassMap = [] // Map untuk warna/border spesifik
    ): void {
        $message = self::getFlash($key); // Ambil DAN HAPUS pesan dari session
        if ($message) {
            $defaultMap = [ 'success' => 'bg-green-50 border-green-400 text-green-800', /* ... map lain ... */ 'error' => 'bg-red-50 border-red-400 text-red-800', 'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800', 'info' => 'bg-blue-50 border-blue-400 text-blue-800' ];
            $effectiveMap = !empty($cssClassMap) ? $cssClassMap : $defaultMap;
            $specificClass = $effectiveMap[$key] ?? 'bg-gray-50 border-gray-300 text-gray-800';
            $safeMessage = SanitizeHelper::html($message);
            $elementId = "flash-{$key}-" . uniqid();

            // Echo div pesan flash dengan ID unik, class styling dasar & spesifik, + transisi
            echo "<div id=\"{$elementId}\" class=\"{$cssClassBase} {$specificClass} transition-opacity duration-500 ease-out opacity-100\">{$safeMessage}</div>";

            // Echo JavaScript inline untuk auto-hide elemen ini (tetap sama)
            $timeoutDuration = ($key === 'error' || $key === 'warning') ? 5000 : 3000; $fadeDuration = 500;
            echo <<<JS
            <script>
                (function() { const el = document.getElementById('{$elementId}'); if(el){ setTimeout(() => { el.style.opacity = '0'; setTimeout(() => { el.remove(); }, {$fadeDuration}); }, {$timeoutDuration}); } })();
            </script>
            JS;
        }
    }

    /**
     * >>> PERUBAHAN DI SINI <<<
     * Menampilkan SEMUA pesan flash dalam satu container overlap.
     */
    public static function displayAllFlashMessages(
         // Class dasar internal (tanpa posisi)
         string $cssClassBase = 'p-4 rounded-md border text-sm',
         array $cssClassMap = [] // Map untuk warna/border spesifik
    ): void {
         self::ensureSessionStarted();
         // Cek apakah ada pesan flash sama sekali
         if (empty($_SESSION[self::FLASH_KEY])) {
             return; // Tidak ada pesan, jangan echo container
         }

         // Echo container untuk CSS overlap
         echo '<div class="flash-message-overlap">'; // Target untuk CSS manual

         $defaultMap = ['success' => '', 'error' => '', 'warning' => '', 'info' => ''];
         $keysToCheck = !empty($cssClassMap) ? array_keys($cssClassMap) : array_keys($defaultMap);

         foreach ($keysToCheck as $key) {
             // Panggil displayFlash dengan class dasar internal
             self::displayFlash($key, $cssClassBase, $cssClassMap);
         }

         echo '</div>'; // Tutup container
     }

} // Akhir kelas
?>