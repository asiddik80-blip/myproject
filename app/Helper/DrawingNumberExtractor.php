<?php

namespace App\Helper;

class DrawingNumberExtractor
{
    /**
     * Mengekstrak semua kandidat drawing-no dari teks OCR
     * Pola: Z322241098, 3222410987, Z322241098-001, 3222410987-001
     *
     * @param string $ocrText
     * @return array ['list' => [...], 'perPageCount' => int, 'candidates' => [...]]
     */
    public static function extract(string $ocrText): array
    {
        preg_match_all('/\b[A-Z]{1}[0-9]{9}(?:-[0-9]{3})?\b/', $ocrText, $matchesAlpha);
        preg_match_all('/\b[0-9]{10}(?:-[0-9]{3})?\b/', $ocrText, $matchesNumeric);

        $foundDrawingNos = array_merge($matchesAlpha[0], $matchesNumeric[0]);
        $drawingNoCandidates = [];

        // Hitung jumlah kemunculan prefix dari drawing-no
        foreach ($foundDrawingNos as $d) {
            $prefix = explode('-', $d)[0];
            $drawingNoCandidates[$prefix] = ($drawingNoCandidates[$prefix] ?? 0) + 1;
        }

        return [
            'list' => $foundDrawingNos,
            'perPageCount' => count($foundDrawingNos),
            'candidates' => $drawingNoCandidates,
        ];
    }
}
