<?php

namespace App\Helper;

class JsonOCRWriter
{
    /**
     * Menyimpan hasil OCR (text, metadata, words, lines) ke file JSON
     *
     * @param string $jsonPath
     * @param array $data
     * @return void
     */
    public static function savePageJson(string $jsonPath, array $data): void
    {
        $jsonDir = dirname($jsonPath);
        if (!is_dir($jsonDir)) {
            mkdir($jsonDir, 0777, true);
        }

        file_put_contents(
            $jsonPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Memperbarui field drawing-no di metadata JSON
     *
     * @param string $jsonPath
     * @param string $drawingNo
     * @return void
     */
    public static function updateDrawingNo(string $jsonPath, string $drawingNo): void
    {
        if (!file_exists($jsonPath)) return;

        $json = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($json)) return;

        $json['metadata']['drawing-no'] = $drawingNo;

        file_put_contents(
            $jsonPath,
            json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
