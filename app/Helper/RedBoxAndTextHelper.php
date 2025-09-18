<?php

namespace App\Helper;

class RedBoxAndTextHelper
{
    /**
     * Menandai visualBox dengan RedBox jika cocok dengan red box,
     * serta mewarnai textLines jika mengandung kata merah.
     *
     * @param array $placardZones Array dari hasil Anchor + OCR
     * @param array $redRegions Array box merah dari RedRegionDetectorHelper
     * @param array $keywords Array kata merah yang dikenali (default: ['CAUTION', 'DANGER', ...])
     * @return array Placard Zones yang telah dimodifikasi
     */
    public static function injectRedInfo(array $placardZones, array $redRegions, array $keywords = ['CAUTION', 'DANGER', 'WARNING']): array
    {
        foreach ($placardZones as &$pz) {
            // 🔴 Cek apakah visualBox sama dengan salah satu redBox (dengan toleransi)
            $hasRedBox = false;
            foreach ($redRegions as $redBox) {
                if (self::boxEquals($pz['visualBox'], $redBox)) {
                    $hasRedBox = true;
                    break;
                }
            }
            if ($hasRedBox) {
                $pz['RedBox'] = true;
            }

            // 🟥 Cek apakah textLines mengandung kata merah
            if (!empty($pz['textLines']) && is_array($pz['textLines'])) {
                foreach ($pz['textLines'] as &$line) {
                    foreach ($keywords as $word) {
                        if (stripos($line['text'] ?? '', $word) !== false) {
                            $line['font-color'] = '#FF1100'; // 🔴 Tandai merah
                            break;
                        }
                    }
                }
                unset($line);
            }
        }

        return $placardZones;
    }

    /**
     * Membandingkan dua box dengan toleransi 2 piksel
     *
     * @param array $a Box pertama (x, y, width, height)
     * @param array $b Box kedua (x, y, width, height)
     * @return bool True jika sama dalam toleransi
     */
    private static function boxEquals(array $a, array $b): bool
    {
        return abs($a['x'] - $b['x']) <= 2 &&
               abs($a['y'] - $b['y']) <= 2 &&
               abs($a['width'] - $b['width']) <= 2 &&
               abs($a['height'] - $b['height']) <= 2;
    }
}
