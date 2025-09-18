<?php

namespace App\Helper;

use App\Data\OCRWhitelist;

class OCRTextEvaluator
{
    const THRESHOLD = 60;

    /**
     * Evaluasi teks hasil OCR: bersihkan, hitung skor, dan tentukan realisme
     *
     * @param string $rawText  Teks mentah dari OCR
     * @return array [
     *     'cleaned' => string,
     *     'score' => int,
     *     'realistic' => bool
     * ]
     */
    public static function evaluate(string $rawText): array
    {
        $cleaned = TextCleaner::clean($rawText);
        $score = 0;

        if ($cleaned === '') {
            return [
                'cleaned' => '',
                'score' => 0,
                'realistic' => false,
            ];
        }

        // Kriteria 1: Uppercase semua
        if (preg_match('/^[A-Z0-9 ]+$/', $cleaned)) {
            $score += 20;
        }

        // Kriteria 2: Panjang karakter 2–30
        $length = strlen($cleaned);
        if ($length >= 2 && $length <= 30) {
            $score += 15;
        }

        // Kriteria 3: Kombinasi huruf dan angka
        if (preg_match('/[A-Z]/', $cleaned) && preg_match('/[0-9]/', $cleaned)) {
            $score += 15;
        }

        // Kriteria 4: Simbol aneh <20%
        $symbolCount = preg_match_all('/[^A-Za-z0-9 ]/', $cleaned);
        if (($symbolCount / max(1, $length)) < 0.2) {
            $score += 20;
        }

        // Kriteria 5: Ada kata whitelist
        $whitelist = OCRWhitelist::get();
        foreach ($whitelist as $word) {
            if (stripos($cleaned, $word) !== false) {
                $score += 20;
                break;
            }
        }

        // Kriteria 6: Tidak multiline
        if (!str_contains($cleaned, "\n")) {
            $score += 10;
        }

        // Kriteria 7: Lebih dari 2 kata
        if (str_word_count($cleaned) > 2) {
            $score += 5;
        }

        return [
            'cleaned' => $cleaned,
            'score' => $score,
            'realistic' => $score >= self::THRESHOLD,
        ];
    }
}
