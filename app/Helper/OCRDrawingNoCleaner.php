<?php

namespace App\Helper;

use App\Data\DrawingNoBlacklist;

class OCRDrawingNoCleaner
{
    public static function cleanDrawNo(array $words, string $drawingNo, int $threshold = 80): array
    {
        return self::cleanDrawNoWithLog($words, $drawingNo, $threshold)['cleaned'];
    }

    public static function cleanDrawNoWithLog(array $words, string $drawingNo, int $threshold = 80): array
    {
        $cleaned = [];
        $noise_detected = [];

        $referenceRaw = strtoupper(trim($drawingNo));
        $referenceNormalized = preg_replace('/[^A-Z0-9]/', '', $referenceRaw);

        $blacklist = DrawingNoBlacklist::get();

        foreach ($words as $word) {
            $textRaw = strtoupper(trim($word['text'] ?? ''));
            $textNormalized = preg_replace('/[^A-Z0-9\-]/', '', $textRaw);
            $normalizedVariant = self::normalizeForBlacklist($textRaw);

            $shouldRemove = false;
            $reason = '';

            // ✅ Hard blacklist check (normalized)
            foreach ($blacklist as $blackItem) {
                $normalizedBlack = self::normalizeForBlacklist($blackItem);
                if ($normalizedVariant === $normalizedBlack) {
                    $shouldRemove = true;
                    $reason = 'Hard blacklist match (normalized)';
                    break;
                }
            }

            // 🔤 Levenshtein similarity
            if (!$shouldRemove) {
                $levDistance = levenshtein($referenceNormalized, $textNormalized);
                $maxLen = max(strlen($referenceNormalized), strlen($textNormalized));
                if ($maxLen > 0) {
                    $similarityPercent = 100 - ($levDistance / $maxLen * 100);
                    if ($similarityPercent >= $threshold) {
                        $shouldRemove = true;
                        $reason = 'Levenshtein similarity ≥ threshold';
                    }
                }
            }

            // 🔠 Longest Common Substring (LCS)
            if (!$shouldRemove && self::longestCommonSubstring($referenceNormalized, $textNormalized) >= 8) {
                $shouldRemove = true;
                $reason = 'LCS ≥ 8';
            }

            // 🔁 Substring minimal 5 karakter
            if (!$shouldRemove) {
                $minSequence = 5;
                for ($i = 0; $i <= strlen($referenceNormalized) - $minSequence; $i++) {
                    $substr = substr($referenceNormalized, $i, $minSequence);
                    if (strpos($textNormalized, $substr) !== false) {
                        $shouldRemove = true;
                        $reason = 'Substring ≥ 5 found';
                        break;
                    }
                }
            }

            // 🔚 Format suffix -xxx (angka)
            if (!$shouldRemove && self::deteksiDrawingNo($textNormalized)) {
                $shouldRemove = true;
                $reason = 'Suffix -xxx pattern';
            }

            // ⛔ Buang kata jika kena salah satu aturan
            if ($shouldRemove) {
                $noise_detected[] = [
                    'original' => $word['text'] ?? '',
                    'normalizedRaw' => $textRaw,
                    'normalizedVariant' => $normalizedVariant,
                    'reason' => $reason,
                ];
                continue;
            }

            $cleaned[] = $word;
        }

        return [
            'cleaned' => $cleaned,
            'noise_detected' => $noise_detected,
        ];
    }

    /**
     * Bersihkan karakter aneh dari kata OCR, ganti dash variasi, buang spasi/tanda baca
     */
    private static function normalizeForBlacklist(string $text): string
    {
        $text = strtoupper($text);
        $text = str_replace(['–', '−', '—'], '-', $text); // normalize dash
        $text = preg_replace('/[[:^print:]\s]+/u', '', $text); // remove invisible & whitespace
        $text = rtrim($text, ",. "); // remove trailing symbols
        return $text;
    }

    /**
     * Hitung Longest Common Substring antara 2 string
     */
    private static function longestCommonSubstring(string $a, string $b): int
    {
        $m = strlen($a);
        $n = strlen($b);
        $result = 0;
        $len = [];

        for ($i = 0; $i <= $m; $i++) {
            $len[$i] = array_fill(0, $n + 1, 0);
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if ($a[$i - 1] === $b[$j - 1]) {
                    $len[$i][$j] = $len[$i - 1][$j - 1] + 1;
                    $result = max($result, $len[$i][$j]);
                }
            }
        }

        return $result;
    }

    /**
     * Deteksi pola drawing-no berdasarkan suffix `-xxx` dengan angka
     */
    public static function deteksiDrawingNo(string $text): bool
    {
        if (preg_match('/-(.{3})$/', $text, $match)) {
            $suffix = $match[1];
            if (preg_match('/\d/', $suffix)) {
                $prefix = substr($text, 0, -4);
                return strlen($prefix) >= 4;
            }
        }
        return false;
    }
}
