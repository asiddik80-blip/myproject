<?php

namespace App\Helper;

class JsonOcrCleaner
{
    /**
     * Membersihkan data lines kosong/spasi dari file JSON OCR.
     *
     * @param string $jsonPath Path ke file JSON
     * @return void
     */
    public static function cleanEmptyLines(string $jsonPath): void
    {
        if (!file_exists($jsonPath)) return;

        $json = json_decode(file_get_contents($jsonPath), true);

        if (!isset($json['lines']) || !is_array($json['lines'])) return;

        // Filter: hanya lines yang tidak kosong
        $json['lines'] = array_values(array_filter($json['lines'], function ($line) {
            $text = trim($line['text'] ?? '');
            return $text !== '';
        }));

        // Simpan ulang file JSON
        file_put_contents($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
