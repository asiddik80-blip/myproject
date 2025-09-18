<?php

namespace App\Helper;

use setasign\Fpdi\Fpdi;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class SplitPDFHelper
{
    /**
     * Split PDF into single-page files.
     *
     * @param string $sourcePdfPath Path to the original PDF
     * @param string $outputDirectory Target directory (e.g., 'storage/app/split_pdfs')
     * @return array [
     *     'success' => bool,
     *     'split_paths' => array of saved paths,
     *     'message' => string
     * ]
     */
    
    //Fungsi split PDF dengan FPDFI untuk menjaga vektor aslinya
    public static function splitPdfWithFPDI(string $sourcePdfPath, string $outputDirectory): array
    {
        try {
            // Validasi file PDF
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception("Source PDF not found: {$sourcePdfPath}");
            }

            // Buat folder output jika belum ada
            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }

            $baseName = pathinfo($sourcePdfPath, PATHINFO_FILENAME);
            $splitPaths = [];

            // Init FPDI untuk hitung total pages
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($sourcePdfPath);

            // Split setiap halaman
            for ($pageNum = 1; $pageNum <= $pageCount; $pageNum++) {
                // Create new PDF instance untuk setiap halaman
                $newPdf = new Fpdi();
                
                // Import halaman specific
                $templateId = $newPdf->importPage($pageNum, '/MediaBox');
                
                // Get size dari template untuk set page size yang tepat
                $templateSize = $newPdf->getTemplateSize($templateId);
                
                // Add page dengan size yang sama dengan original
                $newPdf->AddPage(
                    $templateSize['orientation'], 
                    [$templateSize['width'], $templateSize['height']]
                );
                
                // Use template (ini yang preserve vector content)
                $newPdf->useTemplate($templateId, 0, 0, $templateSize['width'], $templateSize['height']);

                // Generate filename dengan padding
                $pageNumber = str_pad($pageNum, 2, '0', STR_PAD_LEFT);
                $outputPath = "{$outputDirectory}/{$baseName}_page_{$pageNumber}.pdf";
                
                // Save individual page
                $newPdf->Output($outputPath, 'F');
                $splitPaths[] = $outputPath;
                
                // Clear memory untuk halaman ini
                unset($newPdf);
            }

            return [
                'success' => true,
                'split_paths' => $splitPaths,
                'message' => "Split {$pageCount} pages successfully with vector preserved.",
                'total_pages' => $pageCount
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'split_paths' => [],
                'message' => "Error: " . $e->getMessage(),
                'total_pages' => 0
            ];
        }
    }

    /**
     * Split PDF into single-page files with QPDF (preserve vector).
     *
     * @param string $sourcePdfPath Path to the original PDF
     * @param string $outputDirectory Target directory (e.g., 'storage/app/split_pdfs')
     * @return array [
     *     'success' => bool,
     *     'split_paths' => array of saved paths,
     *     'message' => string,
     *     'total_pages' => int
     * ]
     */
    public static function splitPdfWithQPDF(string $sourcePdfPath, string $outputDirectory): array
    {
        try {
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception("Source PDF not found: {$sourcePdfPath}");
            }
            
            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }
            
            // Ambil total halaman PDF
            $process = new Process(['qpdf', '--show-npages', $sourcePdfPath]);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new \Exception("Failed to get page count: " . $process->getErrorOutput());
            }
            
            $pageCount = (int) trim($process->getOutput());
            if ($pageCount <= 0) {
                throw new \Exception("Invalid page count detected.");
            }
            
            $baseName = pathinfo($sourcePdfPath, PATHINFO_FILENAME);
            $splitPaths = [];
            
            // Loop per halaman dan split dengan qpdf
            for ($i = 1; $i <= $pageCount; $i++) {
                $pageNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
                
                $subDir = $outputDirectory . '/' . $baseName;
                if (!file_exists($subDir)) {
                    mkdir($subDir, 0777, true);
                }
                $outputFile = $subDir . '/' . $baseName . '_page_' . $pageNumber . '.pdf';


                
                $process = new Process([
                    'qpdf',
                    '--qdf',
                    '--object-streams=disable',
                    $sourcePdfPath,
                    '--pages', '.', (string)$i, '--',
                    $outputFile
                ]);

                $process->run();
                
                if (!$process->isSuccessful()) {
                    throw new \Exception("Failed to split page {$i}: " . $process->getErrorOutput());
                }
                
                $splitPaths[] = $outputFile;
            }
            
            return [
                'success' => true,
                'split_paths' => $splitPaths,
                'message' => "Split {$pageCount} pages successfully with QPDF (vector preserved).",
                'total_pages' => $pageCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'split_paths' => [],
                'message' => "Error: " . $e->getMessage(),
                'total_pages' => 0
            ];
        }
    }
    

    //DUA SCRIPT SPLIT PDF DENGAN GHOSTSCRIPT
    public static function splitPdfWithGhostscript(string $sourcePdfPath, string $outputDirectory): array
    {
        try {
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception("Source PDF not found: {$sourcePdfPath}");
            }
            
            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }
            
            // Ambil total halaman PDF menggunakan Ghostscript
            $process = new Process([
                'gswin64c', 
                '-q', 
                '-dNODISPLAY', 
                '-c', 
                '(' . $sourcePdfPath . ') (r) file runpdfbegin pdfpagecount = quit'
            ]);
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new \Exception("Failed to get page count: " . $process->getErrorOutput());
            }
            
            $pageCount = (int) trim($process->getOutput());
            if ($pageCount <= 0) {
                throw new \Exception("Invalid page count detected.");
            }
            
            $baseName = pathinfo($sourcePdfPath, PATHINFO_FILENAME);
            $splitPaths = [];
            
            // Loop per halaman dan split dengan Ghostscript
            for ($i = 1; $i <= $pageCount; $i++) {
                $pageNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
                $outputFile = "{$outputDirectory}/{$baseName}_page_{$pageNumber}.pdf";
                
                $process = new Process([
                    'gswin64c',
                    '-sDEVICE=pdfwrite',
                    '-dNOPAUSE',
                    '-dBATCH',
                    '-dSAFER',
                    '-dFirstPage=' . $i,
                    '-dLastPage=' . $i,
                    '-sOutputFile=' . $outputFile,
                    $sourcePdfPath
                ]);
                $process->run();
                
                if (!$process->isSuccessful()) {
                    throw new \Exception("Failed to split page {$i}: " . $process->getErrorOutput());
                }
                
                $splitPaths[] = $outputFile;
            }
            
            return [
                'success' => true,
                'split_paths' => $splitPaths,
                'message' => "Split {$pageCount} pages successfully with Ghostscript.",
                'total_pages' => $pageCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'split_paths' => [],
                'message' => "Error: " . $e->getMessage(),
                'total_pages' => 0
            ];
        }
    }

    // Alternative function dengan optimasi untuk file besar
    public static function splitPdfWithGhostscriptOptimized(string $sourcePdfPath, string $outputDirectory): array
    {
        try {
            if (!file_exists($sourcePdfPath)) {
                throw new \Exception("Source PDF not found: {$sourcePdfPath}");
            }
            
            if (!file_exists($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }
            
            // Method alternatif untuk mendapatkan jumlah halaman (lebih cepat)
            $process = new Process([
                'gs',
                '-q',
                '-dNOSAFER',
                '-dNOPAUSE',
                '-dNODISPLAY',
                '-c',
                "({$sourcePdfPath}) (r) file runpdfbegin pdfpagecount = quit"
            ]);
            $process->run();
            
            if (!$process->isSuccessful()) {
                // Fallback method jika method pertama gagal
                $process = new Process([
                    'gs',
                    '-q',
                    '-dNODISPLAY',
                    '-dNOSAFER',
                    '-dNOPAUSE',
                    '-dNODISPLAY',
                    '-dBATCH',
                    '-sPAPERSIZE=letter',
                    '-dFIXEDMEDIA',
                    '-dPDFFitPage',
                    '-dUseCropBox',
                    '-c',
                    "save pop ({$sourcePdfPath}) (r) file runpdfbegin currentdict /InputAttributes known { InputAttributes /Priority known { InputAttributes /Priority get } if } if pdfpagecount = quit"
                ]);
                $process->run();
                
                if (!$process->isSuccessful()) {
                    throw new \Exception("Failed to get page count: " . $process->getErrorOutput());
                }
            }
            
            $pageCount = (int) trim($process->getOutput());
            if ($pageCount <= 0) {
                throw new \Exception("Invalid page count detected.");
            }
            
            $baseName = pathinfo($sourcePdfPath, PATHINFO_FILENAME);
            $splitPaths = [];
            
            // Split dengan parameter optimasi
            for ($i = 1; $i <= $pageCount; $i++) {
                $pageNumber = str_pad($i, 2, '0', STR_PAD_LEFT);
                $outputFile = "{$outputDirectory}/{$baseName}_page_{$pageNumber}.pdf";
                
                $process = new Process([
                    'gs',
                    '-sDEVICE=pdfwrite',
                    '-dCompatibilityLevel=1.4',
                    '-dPDFSETTINGS=/prepress',  // Untuk kualitas tinggi
                    '-dNOPAUSE',
                    '-dBATCH',
                    '-dSAFER',
                    '-dAutoRotatePages=/None',  // Mencegah rotasi otomatis
                    '-dAutoFilterColorImages=false',
                    '-dColorImageFilter=/FlateEncode',
                    '-dFirstPage=' . $i,
                    '-dLastPage=' . $i,
                    '-sOutputFile=' . $outputFile,
                    $sourcePdfPath
                ]);
                $process->run();
                
                if (!$process->isSuccessful()) {
                    throw new \Exception("Failed to split page {$i}: " . $process->getErrorOutput());
                }
                
                // Verifikasi file output berhasil dibuat
                if (!file_exists($outputFile) || filesize($outputFile) === 0) {
                    throw new \Exception("Output file not created or empty for page {$i}");
                }
                
                $splitPaths[] = $outputFile;
            }
            
            return [
                'success' => true,
                'split_paths' => $splitPaths,
                'message' => "Split {$pageCount} pages successfully with Ghostscript (optimized).",
                'total_pages' => $pageCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'split_paths' => [],
                'message' => "Error: " . $e->getMessage(),
                'total_pages' => 0
            ];
        }
    }

    //**  SELESAI GHOSTSCRIPTS **//

    
    // Alias untuk backward compatibility
    public static function splitPdf(string $sourcePdfPath, string $outputDirectory): array
    {
        return self::splitPdfWithQPDF($sourcePdfPath, $outputDirectory);
    }
}

	
