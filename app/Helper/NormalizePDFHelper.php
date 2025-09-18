<?php

namespace App\Helper;

use Symfony\Component\Process\Process;

class NormalizePDFHelper
{
    /**
     * Normalize 1 halaman PDF
     *
     * @param string $inputPath  Path file PDF input
     * @param string $outputPath Path file PDF hasil normalize
     * @return void
     */
    public static function normalizePdf(string $inputPath, string $outputPath): void
    {
        // Gunakan Ghostscript untuk normalize
        $process = new Process([
            'gswin64c',
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/prepress',
            '-dEmbedAllFonts=true',
            '-dSubsetFonts=false',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-sOutputFile='.$outputPath,
            $inputPath
        ]);


        $process->setEnv([
            'TEMP' => storage_path('app/temp'),
            'TMP'  => storage_path('app/temp'),
        ]);


        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "Normalize gagal: " . $process->getErrorOutput() .
                "\nCommand: " . $process->getCommandLine()
            );
        }

    }


    /**
     * Normalize 1 file PDF utuh menggunakan QPDF
     *
     * @param string $inputPath  Path file PDF input
     * @param string $outputPath Path file PDF hasil normalize
     * @return void
     */
    public static function normalizePDFwithQPDF(string $inputPath, string $outputPath): void
    {
        // Pastikan file input ada
        if (!file_exists($inputPath)) {
            throw new \RuntimeException("File input tidak ditemukan: {$inputPath}");
        }

        // Buat folder output jika belum ada
        $outputDir = dirname($outputPath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Jalankan QPDF dengan opsi normalize
        // --linearize bisa ditambahkan jika ingin optimasi untuk web
        $process = new Process([
            'qpdf',
            '--qdf',                   // QDF mode untuk memudahkan modifikasi
            '--object-streams=disable',// disable object streams
            $inputPath,
            $outputPath
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                "Normalize dengan QPDF gagal: " . $process->getErrorOutput() .
                "\nCommand: " . $process->getCommandLine()
            );
        }
    }
}
