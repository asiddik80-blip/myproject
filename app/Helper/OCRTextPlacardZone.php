<?php

namespace App\Helper;

use Imagick;
use App\Helper\OCRTSVParser;
use App\Helper\OCRTextEvaluator;
use App\Helper\DetectFinalNoise;

class OCRTextPlacardZone
{
    public static function appendTextToPlacards(string $imagePath, array $placardZones): array
    {
        $ocrEngine = 'tesseract';
        $tempDir = storage_path('app/debug/placard_crops');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $img = new Imagick($imagePath);

        foreach ($placardZones as $i => $zone) {
            $vb = $zone['visualBox'];

            // Crop gambar per visualBox
            $cropImg = clone $img;
            $cropImg->cropImage($vb['width'], $vb['height'], $vb['x'], $vb['y']);
            $cropPath = "$tempDir/zone_{$i}.jpg";
            $cropImg->writeImage($cropPath);
            $cropImg->clear();
            $cropImg->destroy();

            // Daftar PSM yang dicoba
            $psmList = [8, 6, 7, 11, 3, 4, 5, 13];

            $finalText = '';
            $bestScore = 0;
            $bestLines = [];
            $psmUsed = null;

            foreach ($psmList as $psm) {
                $tsvPath = "$tempDir/zone_{$i}_psm{$psm}.tsv";
                $cmd = "$ocrEngine " . escapeshellarg($cropPath) . " " . escapeshellarg(str_replace('.tsv', '', $tsvPath)) . " -l eng --oem 1 --psm $psm -c tessedit_create_tsv=1";
                shell_exec($cmd);

                $tsvData = OCRTSVParser::parse($tsvPath);
                $plainText = OCRTSVParser::getPlainText($tsvData);
                $eval = OCRTextEvaluator::evaluate($plainText);

                if ($eval['realistic']) {
                    $finalText = $eval['cleaned'];
                    $bestScore = $eval['score'];

                    // Tambahkan font info ke setiap baris
                    $rawLines = OCRTSVParser::groupByLine($tsvData);
                    $bestLines = array_map(function ($line) {
                        return array_merge($line, [
                            'font-family' => 'Arial',
                            'font-color' => '#000000',
                        ]);
                    }, $rawLines);

                    $psmUsed = $psm;
                    break;
                }

                // Simpan terbaik sementara jika belum realistis
                if ($eval['score'] > $bestScore) {
                    $finalText = $eval['cleaned'];
                    $bestScore = $eval['score'];

                    $rawLines = OCRTSVParser::groupByLine($tsvData);
                    $bestLines = array_map(function ($line) {
                        return array_merge($line, [
                            'font-family' => 'Arial',
                            'font-color' => '#000000',
                        ]);
                    }, $rawLines);

                    $psmUsed = $psm;
                }
            }

            // Simpan hasil ke array
            $placardZones[$i]['textBody'] = $finalText;
            $placardZones[$i]['textLines'] = $bestLines;
            $placardZones[$i]['ocrPsmUsed'] = $psmUsed;
            $placardZones[$i]['realismScore'] = $bestScore;
            $placardZones[$i]['realistic'] = $bestScore >= OCRTextEvaluator::THRESHOLD;
        }

        $img->clear();
        $img->destroy();

        // 🔍 Deteksi noise setelah semua selesai
        $placardZones = DetectFinalNoise::clean($placardZones);

        return $placardZones;
    }
}
