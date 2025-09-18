<?php

namespace App\Helper;

class OcrTextGrouper
{
    /**
     * Fungsi utama untuk mengelompokkan kata berdasarkan baris dan jarak.
     * 
     * @param array $words Daftar kata hasil OCR, masing-masing memiliki text, conf, bbox
     * @param int $toleranceX Batas toleransi horizontal antar kata (dalam pixel)
     * @param int $toleranceY Batas toleransi vertikal antar baris (dalam pixel)
     * @return array Daftar grup baris teks lengkap dengan posisi bounding box
     */
    public static function groupWordsIntoLines(array $words, int $toleranceX = 20, int $toleranceY = 10): array
    {
        // Urutkan kata berdasarkan posisi atas (top) dan kiri (left)
        usort($words, function ($a, $b) {
            if (abs($a['bbox']['top'] - $b['bbox']['top']) > 5) {
                return $a['bbox']['top'] <=> $b['bbox']['top'];
            }
            return $a['bbox']['left'] <=> $b['bbox']['left'];
        });

        $lines = [];

        foreach ($words as $word) {
            $assigned = false;

            foreach ($lines as &$line) {
                // Cek apakah word masuk dalam rentang vertikal toleransi baris
                $lineTop = $line['bbox']['top'];
                $lineBottom = $lineTop + $line['bbox']['height'];

                $wordTop = $word['bbox']['top'];
                $wordBottom = $wordTop + $word['bbox']['height'];

                $overlap = min($lineBottom, $wordBottom) - max($lineTop, $wordTop);
                $averageHeight = ($line['bbox']['height'] + $word['bbox']['height']) / 2;

                if ($overlap > ($averageHeight / 2)) {
                    $line['words'][] = $word;

                    // Perbarui bounding box baris
                    $line['bbox']['left'] = min($line['bbox']['left'], $word['bbox']['left']);
                    $line['bbox']['top'] = min($line['bbox']['top'], $word['bbox']['top']);
                    $line['bbox']['width'] = max(
                        $line['bbox']['left'] + $line['bbox']['width'],
                        $word['bbox']['left'] + $word['bbox']['width']
                    ) - $line['bbox']['left'];
                    $line['bbox']['height'] = max(
                        $line['bbox']['top'] + $line['bbox']['height'],
                        $word['bbox']['top'] + $word['bbox']['height']
                    ) - $line['bbox']['top'];

                    $assigned = true;
                    break;
                }
            }

            // Jika tidak tergabung ke baris manapun, buat baris baru
            if (!$assigned) {
                $lines[] = [
                    'words' => [$word],
                    'bbox' => $word['bbox'],
                ];
            }
        }

        // Gabungkan teks dalam setiap baris
        $result = [];
        foreach ($lines as $line) {
            $text = implode(' ', array_column($line['words'], 'text'));

            $result[] = [
                'text' => trim($text),
                'bbox' => $line['bbox'],
                'word_count' => count($line['words']),
            ];
        }

        return $result;
    }
}
