<?php

namespace App\Helper;

use App\Data\OCRBlacklist;

class DetectFinalNoise
{
    /**
     * Periksa apakah textBody harus dianggap noise akhir
     *
     * @param string|null $textBody
     * @return bool
     */
    public static function isNoise(?string $textBody): bool
    {
        $textBody = trim($textBody ?? '');

        // Hapus karakter non-printable (ASCII 0–31 dan 127)
        $textBody = preg_replace('/[\x00-\x1F\x7F]/u', '', $textBody);

        // Cek apakah satu karakter saja dan termasuk blacklist
        if (mb_strlen($textBody) === 1) {
            $blacklist = OCRBlacklist::get();
            return in_array($textBody, $blacklist);
        }

        return false;
    }

    /**
     * Bersihkan semua placardZones dari hasil OCR yang terlalu singkat dan masuk blacklist
     *
     * @param array $placardZones
     * @return array
     */
    public static function clean(array $placardZones): array
    {
        foreach ($placardZones as $i => $pz) {
            if (self::isNoise($pz['textBody'] ?? null)) {
                $placardZones[$i]['textBody'] = null;
            }
        }

        return $placardZones;
    }
}
