<?php
namespace App\Helpers;

use DateTime;
use DateTimeZone;
use DateInterval;
use Exception;

/**
 * Class DateHelper
 *
 * Menyediakan fungsi untuk memformat tanggal dan waktu.
 */
class DateHelper {

    private static $timezone = 'Asia/Jakarta'; // Sesuaikan dengan timezone Anda

    /**
     * Mengatur timezone default yang akan digunakan.
     *
     * @param string $timezone Identifier timezone (e.g., 'Asia/Jakarta').
     */
    public static function setTimezone(string $timezone): void {
        self::$timezone = $timezone;
    }

    /**
     * Memformat tanggal/waktu ke format Indonesia (e.g., "23 April 2025, 11:30:00").
     *
     * @param string|int|DateTime $dateInput Tanggal/waktu (string format SQL, Unix timestamp, atau objek DateTime).
     * @param string $format Format output sesuai dengan format `DateTime::format()` PHP,
     * atau gunakan format kustom 'full' atau 'short'.
     * @return string|null Tanggal yang diformat atau null jika input tidak valid.
     */
    public static function formatIndonesian(string|int|DateTime $dateInput, string $format = 'full'): ?string {
        try {
            if ($dateInput instanceof DateTime) {
                $date = clone $dateInput; // Hindari modifikasi objek asli
            } elseif (is_numeric($dateInput)) {
                $date = new DateTime('@' . $dateInput); // Dari Unix timestamp
            } else {
                $date = new DateTime($dateInput); // Dari string format SQL
            }

            // Set timezone ke WIB
            $date->setTimezone(new DateTimeZone(self::$timezone));

            // Daftar nama hari dan bulan dalam Bahasa Indonesia
            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            // Format kustom
            if ($format === 'full') {
                 // Format: Senin, 23 April 2025, 11:30:15
                $dayName = $days[$date->format('w')];
                $monthName = $months[$date->format('n')];
                return $dayName . ', ' . $date->format('j') . ' ' . $monthName . ' ' . $date->format('Y, H:i:s');
            } elseif ($format === 'short') {
                 // Format: 23 Apr 2025 11:30
                 $monthShort = substr($months[$date->format('n')], 0, 3);
                 return $date->format('j') . ' ' . $monthShort . ' ' . $date->format('Y H:i');
            } elseif ($format === 'dateonly') {
                 // Format: 23 April 2025
                 $monthName = $months[$date->format('n')];
                 return $date->format('j') . ' ' . $monthName . ' ' . $date->format('Y');
            }

            // Jika format standar PHP yang diberikan
            return $date->format($format);

        } catch (Exception $e) {
            error_log("Date formatting error: " . $e->getMessage());
            return null; // Input tanggal tidak valid
        }
    }

    /**
     * Menghasilkan representasi waktu relatif ("X menit yang lalu", "kemarin", etc.).
     *
     * @param string|int|DateTime $dateInput Tanggal/waktu yang akan dibandingkan dengan waktu sekarang.
     * @return string String waktu relatif (e.g., "5 menit yang lalu").
     */
    public static function timeAgo(string|int|DateTime $dateInput): string {
        try {
            if ($dateInput instanceof DateTime) {
                $date = clone $dateInput;
            } elseif (is_numeric($dateInput)) {
                $date = new DateTime('@' . $dateInput);
            } else {
                $date = new DateTime($dateInput);
            }
            $date->setTimezone(new DateTimeZone(self::$timezone));

            $now = new DateTime('now', new DateTimeZone(self::$timezone));
            $diff = $now->diff($date);

            // Konversi interval ke detik
            $seconds = $diff->s + ($diff->i * 60) + ($diff->h * 3600) + ($diff->d * 86400) + ($diff->m * 2592000) + ($diff->y * 31536000);

             if ($seconds < 60) return $diff->s . " detik yang lalu";
             if ($seconds < 3600) return $diff->i . " menit yang lalu";
             if ($seconds < 86400) return $diff->h . " jam yang lalu";
             if ($seconds < 172800) return "Kemarin pukul " . $date->format('H:i'); // 2*86400
             if ($seconds < 604800) return $diff->d . " hari yang lalu"; // 7*86400
             if ($seconds < 2592000) { // Sekitar 1 bulan
                 $weeks = floor($diff->d / 7);
                 return $weeks . " minggu yang lalu";
             }
             if ($seconds < 31536000) { // Sekitar 1 tahun
                 $months = floor($diff->d / 30); // Perkiraan kasar
                 return $months . " bulan yang lalu";
             }

             return $diff->y . " tahun yang lalu";

        } catch (Exception $e) {
             error_log("Time ago calculation error: " . $e->getMessage());
             return 'beberapa waktu lalu';
        }
    }
}
?>