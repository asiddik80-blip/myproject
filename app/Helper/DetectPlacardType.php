<?php

namespace App\Helper;

class DetectPlacardType
{
    /**
     * Mendeteksi tipe placard berdasarkan ukuran visual box.
     *
     * @param array $visualBox [width, height]
     * @return array [kode-placard, tipe-placard, tag-placard]
     */
    public static function deteksi(array $visualBox): array
    {
        $w = $visualBox['width'];
        $h = $visualBox['height'];

        $placardGroups = include base_path('app/Data/placardType.php');
        unset($placardGroups['refDimension']);

        foreach ($placardGroups as $group) {
            foreach ($group as $type) {
                if (!is_array($type) || !isset($type['width'], $type['height'])) {
                    continue;
                }

                $w_match = $w === $type['width'];
                $h_match = abs($h - $type['height']) <= 2;

                if ($w_match && $h_match) {
                    return [
                        'kode-placard' => $type['kode'],
                        'tipe-placard' => $type['tipe'] ?? null,
                        'tag-placard'  => $type['tag'] ?? null,
                    ];
                }
            }
        }

        return [
            'kode-placard' => null,
            'tipe-placard' => null,
            'tag-placard'  => null,
        ];
    }
}