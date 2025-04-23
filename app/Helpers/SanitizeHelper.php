<?php
namespace App\Helpers;

/**
 * Class SanitizeHelper
 *
 * Menyediakan fungsi untuk membersihkan (sanitize) dan memvalidasi data
 * untuk keamanan aplikasi.
 */
class SanitizeHelper {

    /**
     * Membersihkan string untuk ditampilkan di HTML (mencegah XSS).
     * Mengubah karakter khusus menjadi entitas HTML.
     *
     * @param mixed $data Data yang akan disanitasi. Akan di-cast ke string.
     * @return string String yang aman untuk ditampilkan di HTML.
     */
    public static function html(mixed $data): string {
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Membersihkan string input dasar.
     * Menghapus spasi di awal/akhir dan menghapus tag HTML/PHP.
     *
     * @param mixed $data Data string yang akan disanitasi.
     * @return string String yang sudah dibersihkan.
     */
    public static function string(mixed $data): string {
        return trim(strip_tags((string)$data));
    }

    /**
     * Membersihkan input yang seharusnya berupa email.
     * Menghapus karakter ilegal dari email. TIDAK memvalidasi format.
     *
     * @param mixed $data Input email.
     * @return string String email yang sudah dibersihkan.
     */
    public static function email(mixed $data): string {
        return filter_var(trim((string)$data), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Memvalidasi apakah sebuah string adalah format email yang valid.
     *
     * @param mixed $data String yang akan divalidasi.
     * @return bool True jika valid, false jika tidak.
     */
    public static function is_email(mixed $data): bool {
        return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Membersihkan input yang seharusnya berupa angka integer.
     * Menghapus semua karakter kecuali digit dan tanda plus/minus.
     *
     * @param mixed $data Input angka.
     * @return string|false String angka yang bersih atau false jika gagal.
     */
    public static function integer(mixed $data): string|false {
        return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
    }

     /**
     * Memvalidasi apakah sebuah nilai adalah integer.
     *
     * @param mixed $data Nilai yang akan divalidasi.
     * @return bool True jika integer, false jika tidak.
     */
    public static function is_integer(mixed $data): bool {
         return filter_var($data, FILTER_VALIDATE_INT) !== false;
     }


    /**
     * Membersihkan input yang seharusnya berupa angka float/decimal.
     * Menghapus semua karakter kecuali digit, tanda plus/minus, koma, titik.
     *
     * @param mixed $data Input angka.
     * @param int $flags Filter flags (e.g., FILTER_FLAG_ALLOW_FRACTION).
     * @return string|false String angka yang bersih atau false jika gagal.
     */
    public static function float(mixed $data, int $flags = FILTER_FLAG_ALLOW_FRACTION): string|false {
        return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, $flags);
    }

    /**
     * Memvalidasi apakah sebuah nilai adalah float/decimal.
     *
     * @param mixed $data Nilai yang akan divalidasi.
     * @param int $flags Filter flags (e.g., FILTER_FLAG_ALLOW_FRACTION).
     * @return bool True jika float, false jika tidak.
     */
    public static function is_float(mixed $data, int $flags = FILTER_FLAG_ALLOW_FRACTION): bool {
        return filter_var($data, FILTER_VALIDATE_FLOAT, $flags) !== false;
    }


    /**
     * Membersihkan input yang seharusnya berupa URL.
     * Menghapus karakter ilegal dari URL. TIDAK memvalidasi format.
     *
     * @param mixed $data Input URL.
     * @return string String URL yang sudah dibersihkan.
     */
    public static function url(mixed $data): string {
         return filter_var(trim((string)$data), FILTER_SANITIZE_URL);
    }

    /**
     * Memvalidasi apakah sebuah string adalah format URL yang valid.
     *
     * @param mixed $data String URL yang akan divalidasi.
     * @return bool True jika valid, false jika tidak.
     */
    public static function is_url(mixed $data): bool {
        return filter_var($data, FILTER_VALIDATE_URL) !== false;
    }

     /**
      * Membersihkan array secara rekursif menggunakan htmlspecialchars.
      * Berguna untuk membersihkan seluruh data POST/GET sebelum ditampilkan.
      *
      * @param array $array Input array.
      * @return array Array yang sudah disanitasi.
      */
     public static function htmlRecursive(array $array): array {
         $sanitizedArray = [];
         foreach ($array as $key => $value) {
             if (is_array($value)) {
                 $sanitizedArray[self::html($key)] = self::htmlRecursive($value);
             } else {
                 $sanitizedArray[self::html($key)] = self::html($value);
             }
         }
         return $sanitizedArray;
     }
}
?>