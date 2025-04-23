<?php
namespace App\Helpers;

/**
 * Class StringHelper
 *
 * Menyediakan fungsi bantuan untuk manipulasi string.
 */
class StringHelper {

    /**
     * Memotong string jika melebihi panjang maksimum dan menambahkan elipsis (...).
     *
     * @param string $string String input.
     * @param int $maxLength Panjang maksimum sebelum dipotong.
     * @param string $ellipsis String yang ditambahkan di akhir (default '...').
     * @return string String yang sudah dipotong jika perlu.
     */
    public static function truncate(string $string, int $maxLength = 100, string $ellipsis = '...'): string {
        if (mb_strlen($string) > $maxLength) {
            // Potong string pada panjang maksimum dikurangi panjang elipsis
            $string = mb_substr($string, 0, $maxLength - mb_strlen($ellipsis));
            // Pastikan tidak memotong di tengah kata (opsional)
            $lastSpace = mb_strrpos($string, ' ');
            if ($lastSpace !== false) {
                $string = mb_substr($string, 0, $lastSpace);
            }
            $string .= $ellipsis;
        }
        return $string;
    }

    /**
     * Membuat versi "slug" dari sebuah string (lowercase, alphanumeric, dash separated).
     * Berguna untuk membuat URL yang SEO-friendly.
     *
     * @param string $string String input (e.g., "Nama Menu Spesial!").
     * @param string $separator Karakter pemisah (default '-').
     * @return string Slug (e.g., "nama-menu-spesial").
     */
    public static function slugify(string $string, string $separator = '-'): string {
        // 1. Ganti karakter non-letter atau non-digit dengan separator
        $slug = preg_replace('/[^a-zA-Z0-9]+/', $separator, $string);
        // 2. Hapus separator di awal/akhir
        $slug = trim($slug, $separator);
        // 3. Konversi ke lowercase
        $slug = strtolower($slug);
        // 4. Jika kosong, beri nama default
        if (empty($slug)) {
            return 'n-a';
        }
        return $slug;
    }
}
?>