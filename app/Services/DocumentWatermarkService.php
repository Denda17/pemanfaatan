<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class DocumentWatermarkService
{
    /**
     * Terapkan watermark pelindungan data pribadi (UU PDP) pada gambar dokumen (KTP, NPWP, dsb)
     * sebelum disimpan ke media penyimpanan permanen.
     */
    public function applyWatermarkIfImage(UploadedFile $file, string $bookingCode): void
    {
        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return;
        }

        try {
            $path = $file->getRealPath();
            if (! $path || ! file_exists($path)) {
                return;
            }

            $image = match ($mime) {
                'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : null,
                'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : null,
                'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
                default => null,
            };

            if (! $image) {
                return;
            }

            // Auto-rotate sesuai EXIF jika ada (foto kamera HP)
            if (function_exists('exif_read_data') && $mime === 'image/jpeg') {
                $exif = @exif_read_data($path);
                if (! empty($exif['Orientation'])) {
                    $image = match ($exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            }

            $width = imagesx($image);
            $height = imagesy($image);

            imagealphablending($image, true);
            imagesavealpha($image, true);

            $watermarkText = sprintf(
                'HANYA UNTUK VERIFIKASI SEWA ASET PEMPROV JABAR | %s | %s',
                $bookingCode,
                now()->format('d/m/Y')
            );

            // Banner watermark di bagian bawah
            $bannerHeight = max(36, (int) ($height * 0.08));
            $y1 = max(0, $height - $bannerHeight - 15);
            $y2 = min($height, $y1 + $bannerHeight);

            // Kotak latar semi-transparan (gelap)
            $overlayColor = imagecolorallocatealpha($image, 15, 23, 42, 45); // ~65% opasitas
            imagefilledrectangle($image, 0, $y1, $width, $y2, $overlayColor);

            // Garis aksen emas/hijau di atas banner
            $accentColor = imagecolorallocatealpha($image, 16, 185, 129, 20); // emerald-500
            imageline($image, 0, $y1, $width, $y1, $accentColor);
            imageline($image, 0, $y1 + 1, $width, $y1 + 1, $accentColor);

            // Teks watermark warna putih tebal
            $textColor = imagecolorallocate($image, 255, 255, 255);
            $font = 5; // Built-in font terbesar (9x15 px)
            $fontWidth = imagefontwidth($font);
            $fontHeight = imagefontheight($font);
            $textWidth = strlen($watermarkText) * $fontWidth;

            $textX = max(12, (int) (($width - $textWidth) / 2));
            $textY = (int) ($y1 + (($bannerHeight - $fontHeight) / 2));

            imagestring($image, $font, $textX, $textY, $watermarkText, $textColor);

            // Simpan kembali ke file temporer jika driver format tersedia
            match ($mime) {
                'image/jpeg' => function_exists('imagejpeg') ? imagejpeg($image, $path, 92) : null,
                'image/png' => function_exists('imagepng') ? imagepng($image, $path, 7) : null,
                'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, 90) : null,
                default => null,
            };

            imagedestroy($image);
        } catch (\Throwable $e) {
            Log::warning('Gagal membubuhkan watermark dokumen: '.$e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'booking_code' => $bookingCode,
            ]);
        }
    }
}
