<?php
// File: app/Helpers/QrCodeHelper.php (Perbaikan Final v2 - RoundBlockSizeMode)
namespace App\Helpers;

// Import kelas-kelas yang dibutuhkan DENGAN BENAR
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelInterface;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin; // <-- Import kelas spesifik ini
// HAPUS atau jangan gunakan: use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Exception;
use App\Helpers\SanitizeHelper;

/**
 * Class QrCodeHelper
 * Membantu membuat QR Code menggunakan library endroid/qr-code.
 */
class QrCodeHelper {

    /**
     * Menghasilkan data QR Code sebagai objek ResultInterface.
     */
    public static function generate(
        string $data,
        int $size = 200,
        string $format = 'png',
        ErrorCorrectionLevelInterface $errorCorrectionLevel = new ErrorCorrectionLevelHigh()
    ): ?ResultInterface {
        try {
            $writer = ($format === 'svg') ? new SvgWriter() : new PngWriter();

            $builder = Builder::create()
                ->writer($writer)
                ->writerOptions([])
                ->data($data)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel($errorCorrectionLevel)
                ->size($size)
                ->margin(10)
                // === PERBAIKAN DI SINI: Gunakan instance baru ===
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                // ================================================
                ; // Akhir dari chain method builder

            return $builder->build();

        } catch (\Throwable $e) {
             error_log("QR Code generation failed: " . $e->getMessage());
             return null;
        }
    }

    /**
     * Menampilkan gambar QR Code (PNG atau SVG) langsung ke browser.
     */
    public static function display(string $data, int $size = 250, string $format = 'png'): void {
        // Buat instance baru dari ErrorCorrectionLevelHigh saat memanggil generate
        $result = self::generate($data, $size, $format, new ErrorCorrectionLevelHigh());

        if ($result) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();
            exit;
        } else {
            http_response_code(500);
            header('Content-Type: text/plain');
            // Tampilkan pesan error yang lebih spesifik jika memungkinkan,
            // tapi untuk sekarang ini cukup.
            echo "Error: Gagal membuat QR Code.";
            exit;
        }
    }

    /**
     * Menghasilkan tag <img> dengan data URI QR code (format PNG).
     */
    public static function getImgTagPngDataUri(string $data, int $size = 150, string $alt = 'QR Code'): string {
        $result = self::generate($data, $size, 'png', new ErrorCorrectionLevelHigh());
        if ($result) {
             $safeAlt = SanitizeHelper::html($alt);
            return "<img src=\"{$result->getDataUri()}\" alt=\"{$safeAlt}\" width=\"{$size}\" height=\"{$size}\">";
        }
        return '';
    }
}
?>