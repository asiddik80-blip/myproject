<?php

namespace App\Helper;

class RedRegionDetectorHelper
{
    /**
     * Jalankan script Python untuk mendeteksi area berwarna merah pada gambar.
     *
     * @param string $imagePath Path absolut ke file gambar
     * @return array Array bounding box [ [x, y, width, height], ... ]
     */
    public static function detectRedRegions(string $imagePath): array
    {
        // Path ke Python dan script deteksi merah
        $pythonPath = 'python'; // sesuaikan jika di Windows pakai python3 atau python.exe
        $scriptPath = base_path('app/Script/Python/detect_red_mask.py');

        // Pastikan path aman (dalam kutipan)
        $command = "$pythonPath " . escapeshellarg($scriptPath) . ' ' . escapeshellarg($imagePath);

        // Jalankan perintah
        $output = shell_exec($command);

        // Jika tidak ada output, kembalikan array kosong
        if (!$output) {
            return [];
        }

        // Coba decode JSON
        $result = json_decode($output, true);

        // Jika gagal decode, atau bukan array, kembalikan array kosong
        return is_array($result) ? $result : [];
    }
}
