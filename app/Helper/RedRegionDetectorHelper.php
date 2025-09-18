<?php

namespace App\Helper;

class RedRegionDetectorHelper
{
    /**
     * Menjalankan deteksi area merah dengan Python
     *
     * @param string $imagePath Path penuh ke file gambar JPEG/PNG
     * @return array Array of bounding boxes (x, y, width, height)
     */
    public static function detectRedRegions(string $imagePath): array
    {
        // Path ke Python dan script
        $pythonPath = 'python'; // Ganti jika Anda pakai 'python3'
        $scriptPath = base_path('app/Script/Python/detect_red_mask.py');

        // Escape argumen
        $escapedImagePath = escapeshellarg($imagePath);

        // Susun command
        $command = "$pythonPath $scriptPath $escapedImagePath";

        // Jalankan perintah dan ambil output
        $output = shell_exec($command);

        // Coba decode hasil JSON
        $boxes = json_decode($output, true);

        // Validasi hasil
        if (!is_array($boxes)) {
            return [];
        }

        return $boxes;
    }
}
