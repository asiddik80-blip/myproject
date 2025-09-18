<?php

namespace App\Helper;

class ImagePreprocessorHelper
{
    /**
     * Menjalankan visual preprocessing dan deteksi box dengan Python
     *
     * @param string $imagePath Path penuh ke file JPEG
     * @param string|null $zoneJsonPath Path penuh ke file zona (jika digunakan)
     * @return array Array of bounding boxes (x, y, width, height)
     */
    public static function runVisualPreprocessing(string $imagePath, string $zoneJsonPath = null): array
    {
        // Path ke Python dan script
        $pythonPath = 'python'; // bisa diganti ke 'python3' jika perlu
        $scriptPath = base_path('app/Script/Python/detectBoxesTwoSteps.py');

        // Escape argumen
        $escapedImagePath = escapeshellarg($imagePath);
        $escapedZonePath = $zoneJsonPath ? escapeshellarg($zoneJsonPath) : '';

        // Susun command
        $command = "$pythonPath $scriptPath $escapedImagePath $escapedZonePath";

        // Jalankan perintah
        $output = shell_exec($command);

        // Coba decode hasil
        $boxes = json_decode($output, true);

        // Validasi hasil
        if (!is_array($boxes)) {
            return []; // atau throw exception jika ingin ketat
        }

        return $boxes;
    }
}
