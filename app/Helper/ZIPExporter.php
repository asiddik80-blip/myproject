<?php

namespace App\Helper;

use ZipArchive;
use Illuminate\Support\Facades\Response;

class ZIPExporter
{
    /**
     * Membuat file ZIP berisi semua file .ai hasil konversi
     *
     * @param array $filenames Daftar nama file (tanpa ekstensi .ai)
     * @param string $sourceDir Folder tempat file .ai berada
     * @param string $zipOutputPath Path lengkap file ZIP yang akan dibuat
     * @return string Path ZIP yang berhasil dibuat
     */
    public static function createZip(array $filenames, string $sourceDir, string $zipOutputPath): string
    {
        $zip = new ZipArchive;

        if ($zip->open($zipOutputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal membuat ZIP file: {$zipOutputPath}");
        }

        foreach ($filenames as $filename) {
            $aiPath = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename . '.ai';
            if (file_exists($aiPath)) {
                $zip->addFile($aiPath, $filename . '.ai');
            }
        }

        $zip->close();
        return $zipOutputPath;
    }

    /**
     * Mengembalikan response download dan otomatis hapus ZIP setelah dikirim
     *
     * @param string $zipPath
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function downloadAndDelete(string $zipPath)
    {
        return Response::download($zipPath)->deleteFileAfterSend(true);
    }
}
