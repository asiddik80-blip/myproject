<?php

namespace App\Helper;

class OcrPdfMetadataBuilder
{
    /**
     * Generate file JSON metadata untuk 1 file PDF secara keseluruhan.
     *
     * @param string $outputFolder       Path tempat menyimpan file JSON
     * @param string $filename           Nama file PDF (tanpa ekstensi .pdf)
     * @param string $originalName       Nama asli file saat diupload
     * @param int    $totalPages         Jumlah halaman
     * @param int    $filesizeKb         Ukuran file PDF dalam KB
     * @param string $dimensions         Ukuran PDF (misalnya "3508 x 2480 pt")
     * @param string $finalDrawingNo     Drawing-no utama yang terdeteksi
     * @param array  $drawingNoPerPage   Mapping jumlah drawing-no per halaman
     * @param array  $pageTypeSummary    Total per jenis halaman (C, D, R)
     * @param array  $pageTypeDetail     Mapping jenis halaman per nomor halaman
     * @param float  $processingTime     Lama pemrosesan PDF (dalam detik)
     * @param array  $imagePages         Daftar path file image halaman (jpg)
     * 
     * @return void
     */
    public static function generate(
        string $outputFolder,
        string $filename,
        string $originalName,
        int $totalPages,
        int $filesizeKb,
        string $dimensions,
        string $finalDrawingNo,
        array $drawingNoPerPage,
        array $pageTypeSummary,
        array $pageTypeDetail,
        float $processingTime,
        array $imagePages
    ): void
    {
        $metadata = [
            'stored_filename' => "{$filename}.pdf",
            'original_filename' => $originalName,
            'upload_timestamp' => now()->format('Y-m-d H:i:s'),
            'total_pages' => $totalPages,
            'pdf_filesize_kb' => $filesizeKb,
            'pdf_dimensions' => $dimensions,
            'drawing-no' => $finalDrawingNo,
            'drawing_no_total' => array_sum($drawingNoPerPage),
            'drawing_no_per_page' => $drawingNoPerPage,
            'page_type_distribution' => $pageTypeSummary,
            'page_type_detail' => $pageTypeDetail,
            'ocr_engine' => 'Tesseract v5.3.1 (TSV)',
            'processing_duration' => round($processingTime, 2) . 's',
            'halaman_terdeteksi' => array_map('basename', $imagePages),
        ];

        $jsonPath = $outputFolder . "/{$filename}_metadata.json";
        file_put_contents($jsonPath, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
