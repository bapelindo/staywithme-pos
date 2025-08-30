<?php
// File: app/Helpers/QrCodeHelper.php (Perbaikan Final v2 - RoundBlockSizeMode)
namespace App\Helpers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelInterface;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin; // PERBAIKAN: Import kelas spesifik
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\Result\ResultInterface;
use Exception;
use App\Helpers\SanitizeHelper;

class QrCodeHelper {

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
                // PERBAIKAN: Gunakan instance baru dari kelas yang benar
                ->roundBlockSizeMode(new RoundBlockSizeModeMargin());

            return $builder->build();

        } catch (\Throwable $e) {
             error_log("QR Code generation failed: " . $e->getMessage());
             return null;
        }
    }

    public static function display(string $data, int $size = 250, string $format = 'png'): void {
        $result = self::generate($data, $size, $format, new ErrorCorrectionLevelHigh());

        if ($result) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Type: ' . $result->getMimeType());
            echo $result->getString();
            exit;
        } else {
            http_response_code(500);
            header('Content-Type: text/plain');
            echo "Error: Gagal membuat QR Code.";
            exit;
        }
    }

    public static function getImgTagPngDataUri(string $data, int $size = 150, string $alt = 'QR Code'): string {
        $result = self::generate($data, $size, 'png', new ErrorCorrectionLevelHigh());
        if ($result) {
             $safeAlt = SanitizeHelper::html($alt);
            return "<img src=\"{$result->getDataUri()}\" alt=\"{$safeAlt}\" width=\"{$size}\" height=\"{$size}\">";
        }
        return '';
    }
}