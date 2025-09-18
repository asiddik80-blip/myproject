<?php

namespace App\Helper;

use Illuminate\Support\Facades\Storage;

class ScanZoneMapHelper
{
    const REFERENCE_WIDTH = 3508;
    const REFERENCE_HEIGHT = 2480;

    /**
     * Zona referensi tetap (untuk diskalakan berdasarkan ukuran gambar)
     */
    public static function getReferenceZones(): array
    {
        return [
            [
                "name" 	=> "Zona Paper",
                "type" 	=> "paper",
                "x" 	=> 1,
                "y" 	=> 1,
                "width" => 3395,
                "height" => 2348
            ],
            [
                "name" => "Zona Milimeter",
                "type" => "milimeter",
                "x" => 331,
                "y" => 2129,
                "width" => 902,
                "height" => 223
            ]
        ];
    }

    /**
     * Ambil zona dalam skala sesuai dimensi gambar yang diupload
     */
    public static function getZonesFromImage(string $imagePath): array
    {
        [$imgWidth, $imgHeight] = getimagesize($imagePath);
        $scaleX = $imgWidth / self::REFERENCE_WIDTH;
        $scaleY = $imgHeight / self::REFERENCE_HEIGHT;

        return array_map(function ($zone) use ($scaleX, $scaleY) {
            return [
                "name"   => $zone['name'],
                "type"   => $zone['type'],
                "x"      => (int) round($zone['x'] * $scaleX),
                "y"      => (int) round($zone['y'] * $scaleY),
                "width"  => (int) round($zone['width'] * $scaleX),
                "height" => (int) round($zone['height'] * $scaleY),
            ];
        }, self::getReferenceZones());
    }

    /**
     * Eksport ke file JSON
     */
    public static function exportZonesToJson(string $fullPath)
    {
        $zones = self::getReferenceZones();
        file_put_contents($fullPath, json_encode($zones, JSON_PRETTY_PRINT));
    }
}
