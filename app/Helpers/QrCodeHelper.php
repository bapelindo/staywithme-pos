<?php
namespace App\Helpers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\Result\ResultInterface; // Import ResultInterface
use Exception; // Import Exception

/**
 * Class QrCodeHelper
 *
 * Membantu membuat QR Code menggunakan library endroid/qr-code.
 */
class QrCodeHelper {

    /**
     * Menghasilkan data QR Code sebagai objek ResultInterface.
     *
     * @param string $data Data yang akan di-encode (biasanya URL).
     * @param int $size Ukuran gambar QR Code dalam piksel.
     * @param string $format 'png' atau 'svg'.
     * @param ErrorCorrectionLevel $errorCorrectionLevel Tingkat koreksi error.
     * @return ResultInterface|null Objek hasil QR Code atau null jika error.
     */
    public static function generate(
        string $data,
        int $size = 200,
        string $format = 'png',
        ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::High
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
                ->margin(10) // Margin di sekitar QR code
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);
                // ->logoPath(__DIR__.'/path/to/logo.png') // Opsional: Path ke logo
                // ->logoResizeToWidth(50)

            return $builder->build();

        } catch (Exception $e) {
             error_log("QR Code generation failed: " . $e->getMessage());
             return null;
        }
    }

    /**
     * Menampilkan gambar QR Code (PNG atau SVG) langsung ke browser.
     *
     * @param string $data Data yang akan di-encode.
     * @param int $size Ukuran gambar.
     * @param string $format 'png' atau 'svg'.
     */
    public static function display(string $data, int $size = 200, string $format = 'png'): void {
        $result = self::generate($data, $size, $format);

        if ($result) {
            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();
            exit; // Hentikan eksekusi setelah output gambar
        } else {
            // Handle error, mungkin tampilkan gambar placeholder atau pesan error
            http_response_code(500);
            echo "Gagal membuat QR Code.";
            exit;
        }
    }

    /**
     * Menghasilkan tag <img> dengan data URI QR code (format PNG).
     * Berguna untuk menyematkan QR code langsung di HTML tanpa file terpisah.
     *
     * @param string $data Data yang akan di-encode.
     * @param int $size Ukuran gambar.
     * @param string $alt Teks alternatif untuk tag img.
     * @return string Tag <img> HTML atau string kosong jika gagal.
     */
    public static function getImgTagPngDataUri(string $data, int $size = 150, string $alt = 'QR Code'): string {
        $result = self::generate($data, $size, 'png');
        if ($result) {
            return '<img src="' . $result->getDataUri() . '" alt="' . SanitizeHelper::html($alt) . '" width="' . $size . '" height="' . $size . '">';
        }
        return ''; // Gagal generate
    }
}
?>