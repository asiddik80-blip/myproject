<?php

namespace App\Helper;

class OCRTSVParser
{
    public static function parse(string $tsvPath): array
    {
        if (!file_exists($tsvPath)) {
            return [];
        }

        $lines = file($tsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) return [];

        $headers = str_getcsv(array_shift($lines), "\t");
        $ocrData = [];

        foreach ($lines as $line) {
            $fields = str_getcsv($line, "\t");
            $entry = array_combine($headers, $fields);

            if (!empty($entry['text']) && intval($entry['conf']) > 30) {
                $ocrData[] = [
                    'text' => $entry['text'],
                    'conf' => (int) $entry['conf'],
                    'top' => (int) $entry['top'],
                    'bbox' => [
                        'left' => (int) $entry['left'],
                        'top' => (int) $entry['top'],
                        'width' => (int) $entry['width'],
                        'height' => (int) $entry['height'],
                    ]
                ];
            }
        }

        return $ocrData;
    }

    public static function getPlainText(array $ocrData): string
    {
        return trim(implode(' ', array_column($ocrData, 'text')));
    }

    /**
     * Kelompokkan teks berdasarkan baris (posisi top secara vertikal)
     */
    public static function groupByLine(array $ocrData): array
    {
        // Kelompokkan berdasarkan posisi Y (top)
        $lines = [];
        $lineNo = 1;

        usort($ocrData, fn($a, $b) => $a['bbox']['top'] <=> $b['bbox']['top']);

        $tolerance = 10;
        $currentTop = null;

        foreach ($ocrData as $word) {
            $top = $word['bbox']['top'];
            $text = $word['text'];

            if ($currentTop === null || abs($top - $currentTop) > $tolerance) {
                // Baris baru
                $lines[$lineNo] = [$text];
                $currentTop = $top;
                $lineNo++;
            } else {
                // Tambahkan ke baris terakhir
                $lines[$lineNo - 1][] = $text;
            }
        }

        $result = [];
        $i = 1;
        foreach ($lines as $line) {
            $result[] = [
                'line_no' => $i++,
                'text' => implode(' ', $line),
                'role' => 'body'
            ];
        }

        return $result;
    }



}
