<?php
namespace App\Helpers;

/**
 * Class NumberHelper
 *
 * Menyediakan fungsi untuk memformat angka dan mata uang.
 */
class NumberHelper {

    /**
     * Memformat angka menjadi format mata uang Rupiah (IDR).
     * Contoh: 150000 -> "Rp 150.000"
     *
     * @param float|int|string $number Angka yang akan diformat.
     * @param bool $includeSymbol Apakah menyertakan simbol 'Rp '.
     * @param int $decimals Jumlah digit desimal (biasanya 0 untuk Rupiah).
     * @return string Angka dalam format Rupiah.
     */
    public static function formatCurrencyIDR(float|int|string $number, bool $includeSymbol = true, int $decimals = 0): string {
        $cleanedNumber = filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($cleanedNumber === false || !is_numeric($cleanedNumber)) {
            return ($includeSymbol ? 'Rp ' : '') . '0'; // Default jika input tidak valid
        }

        $formatted = number_format((float)$cleanedNumber, $decimals, ',', '.');
        return ($includeSymbol ? 'Rp ' : '') . $formatted;
    }

     /**
     * Memformat angka dengan pemisah ribuan dan desimal sesuai locale Indonesia.
     * Contoh: 12345.67 -> "12.345,67"
     *
     * @param float|int|string $number Angka yang akan diformat.
     * @param int $decimals Jumlah digit desimal.
     * @return string Angka yang diformat.
     */
    public static function formatNumber(float|int|string $number, int $decimals = 0): string {
        $cleanedNumber = filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
         if ($cleanedNumber === false || !is_numeric($cleanedNumber)) {
            return '0'; // Default jika input tidak valid
        }
        return number_format((float)$cleanedNumber, $decimals, ',', '.');
    }
}

?>