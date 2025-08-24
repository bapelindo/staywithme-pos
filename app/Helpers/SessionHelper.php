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
    public static function hasFlash(string $key): bool { self::ensureSessionStarted(); return isset($_SESSION[self::FLASH_KEY][$key]); }

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
    public static function hasFlashData(string $key): bool { self::ensureSessionStarted(); return isset($_SESSION[self::FLASH_DATA_KEY][$key]); }

    
    // PERBAIKAN: Menampilkan pesan flash dengan auto-hide JS
    public static function displayFlash(
        string $key,
        string $cssClassBase = 'p-4 rounded-md border text-sm',
        array $cssClassMap = []
    ): void {
        $message = self::getFlash($key);
        if ($message) {
            $defaultMap = [ 'success' => 'bg-green-50 border-green-400 text-green-800', 'error' => 'bg-red-50 border-red-400 text-red-800', 'warning' => 'bg-yellow-50 border-yellow-400 text-yellow-800', 'info' => 'bg-blue-50 border-blue-400 text-blue-800' ];
            $effectiveMap = !empty($cssClassMap) ? $cssClassMap : $defaultMap;
            $specificClass = $effectiveMap[$key] ?? 'bg-gray-50 border-gray-300 text-gray-800';
            $safeMessage = SanitizeHelper::html($message);
            $elementId = "flash-{$key}-" . uniqid();

            echo "<div id=\"{$elementId}\" class=\"{$cssClassBase} {$specificClass} transition-opacity duration-500 ease-out opacity-100\">{$safeMessage}</div>";

            $timeoutDuration = ($key === 'error' || $key === 'warning') ? 5000 : 3000; $fadeDuration = 500;
            echo <<<JS
            <script>
                (function() { const el = document.getElementById('{$elementId}'); if(el){ setTimeout(() => { el.style.opacity = '0'; setTimeout(() => { el.remove(); }, {$fadeDuration}); }, {$timeoutDuration}); } })();
            </script>
            JS;
        }
    }

    // PERBAIKAN: Menampilkan SEMUA pesan flash dalam satu container overlap
    public static function displayAllFlashMessages(
         string $cssClassBase = 'p-4 rounded-md border text-sm',
         array $cssClassMap = []
    ): void {
         self::ensureSessionStarted();
         if (empty($_SESSION[self::FLASH_KEY])) {
             return;
         }

         // Echo container untuk CSS overlap (posisi diatur di admin_layout.php)
         echo '<div class="flash-message-overlap">';

         $defaultMap = ['success' => '', 'error' => '', 'warning' => '', 'info' => ''];
         $keysToCheck = !empty($cssClassMap) ? array_keys($cssClassMap) : array_keys($defaultMap);

         foreach ($keysToCheck as $key) {
             self::displayFlash($key, $cssClassBase, $cssClassMap);
         }

         echo '</div>';
     }
}