<?php

namespace App\Helper;

use Imagick;

class AnchorFromVisualBoxHelper
{
    /**
     * Deteksi anchor ITEM-xxx di bawah setiap visual box.
     * Menggunakan crop area dan OCR ulang (lepas dari OCR JSON awal).
     */
    public static function detect(string $imagePath, array $visualBoxes, array $paperZone): array
    {
        $placardZones = [];
        $ocrEngine = 'tesseract'; // pastikan tersedia di PATH
        $tempDir = storage_path('app/debug/anchor_crops');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $img = new Imagick($imagePath);

        foreach ($visualBoxes as $index => $vb) {
            // Tentukan area crop di bawah VB (tinggi 300px)
            $cropX = $vb['x'];
            $cropY = $vb['y'] + $vb['height'] + 5;
            $cropW = $vb['width'];
            $cropH = 300;

            // Jangan keluar dari zona paper
            $maxY = $paperZone['y'] + $paperZone['height'];
            if ($cropY + $cropH > $maxY) {
                $cropH = $maxY - $cropY;
            }
            if ($cropH <= 0) continue;

            // Crop area dan simpan sementara
            $cropImg = clone $img;
            $cropImg->cropImage($cropW, $cropH, $cropX, $cropY);
            $cropImgPath = $tempDir . "/vb_{$index}_anchor_crop.jpg";
            $cropImg->writeImage($cropImgPath);
            $cropImg->clear();
            $cropImg->destroy();

            // OCR tesseract (plain text saja)
            $outputTxt = $tempDir . "/vb_{$index}_ocr";
            shell_exec("{$ocrEngine} " . escapeshellarg($cropImgPath) . " " . escapeshellarg($outputTxt) . " -l eng --psm 6");

            $ocrText = @file_get_contents("{$outputTxt}.txt");
            if (!$ocrText) continue;

            // Cek apakah ada teks ITEM-xxx
            if (preg_match('/ITEM-\d{3}/', $ocrText, $matches)) {
                $foundText = $matches[0];

                // Estimasi anchor box
                $anchorBox = [
                    'x' => $cropX,
                    'y' => $cropY + 5,
                    'width' => $cropW,
                    'height' => 60,
                ];

                $placardZones[] = [
                    'visualBox' => $vb,
                    'anchorBox' => $anchorBox,
                    'anchorText' => $foundText,
                ];
            }
        }

        return $placardZones;
    }
}
