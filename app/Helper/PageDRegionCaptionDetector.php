<?php

namespace App\Helper;

class PageDRegionCaptionDetector
{
    /**
     * Deteksi semua caption seperti ITEM-xxx berdasarkan OCR data
     * Menggunakan kombinasi lines[] untuk menemukan kandidat, dan words[] untuk akurasi bbox
     */
    public static function detect(array $ocrData, string $imagePath): array
    {
        $results = [];

        // 1. Cari semua line yang mengandung teks ITEM-xxx (sebagai kandidat blok caption)
        $candidateLines = collect($ocrData['lines'] ?? [])->filter(function ($line) {
            return preg_match('/ITEM-\d{3}/', $line['text'] ?? '') === 1;
        });

        // 2. Dari lines kandidat, cari words[] yang merupakan ITEM-xxx
        $captions = collect($ocrData['words'] ?? [])->filter(function ($word) {
            return preg_match('/^ITEM-\d{3}$/', $word['text'] ?? '') === 1;
        });

        // 3. Untuk setiap caption, simpan info text, bbox, confidence
        foreach ($captions as $caption) {
            $bbox = $caption['bbox'] ?? null;
            if (!$bbox) continue;

            $results[] = [
                'text' => $caption['text'],
                'bounding_box' => [
                    'x' => $bbox['left'],
                    'y' => $bbox['top'],
                    'width' => $bbox['width'],
                    'height' => $bbox['height'],
                ],
                'ocr_confidence' => $caption['conf'] ?? null,
            ];
        }

        return $results;
    }
}
