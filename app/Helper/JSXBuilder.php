<?php

namespace App\Helper;

class JSXBuilder
{
    /**
     * Generate JSX script dari template
     *
     * @param array $fileList
     * @param string $templatePath
     * @param string $outputPath
     * @return string Full path file JSX yang dihasilkan
     */
    public static function build(array $fileList, string $templatePath, string $outputPath): string
    {
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found: {$templatePath}");
        }

        $template = file_get_contents($templatePath);

        // Prepare files array (as proper JS array -> JSON)
        $filesForJs = [];
        $allPdfPathsFlat = [];

        foreach ($fileList as $file) {
            // normalisasi path (forward slashes) dan prefix file:///
            $originalInput = isset($file['originalInput']) ? str_replace('\\', '/', $file['originalInput']) : '';
            $output = isset($file['output']) ? str_replace('\\', '/', $file['output']) : '';


            // halamanPDF bisa berada di 'halamanPDF' atau 'input' - prioritaskan 'halamanPDF'
            $halaman = [];
            $rawHalaman = $file['halamanPDF'] ?? ($file['input'] ?? []);
            foreach ($rawHalaman as $p) {
                $pNorm = str_replace('\\', '/', $p); // Hanya forward slash
                $halaman[] = $pNorm;
                $allPdfPathsFlat[] = $pNorm;
            }


            $filesForJs[] = [
                'originalInput' => $originalInput,
                'output' => $output,
                'halamanPDF' => $halaman,
                'totalPages' => (int)($file['totalPages'] ?? count($halaman)),
                'width' => (float)($file['width'] ?? 841.68),
                'height' => (float)($file['height'] ?? 594.72),
            ];
        }

        // JSON untuk variable "files" (langsung array lengkap)
        $jsFilesJson = json_encode($filesForJs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Untuk ALL_SPLIT_PDF_PATHS: buat string item tanpa tanda kurung luar
        // (template sudah punya [ {{ALL_SPLIT_PDF_PATHS}} ])
        $allPdfQuoted = array_map(fn($p) => '"' . $p . '"', $allPdfPathsFlat);
        $allPdfItemsString = implode(', ', $allPdfQuoted); // <-- tanpa [ ]

        // Ganti placeholder di template
        $jsxContent = str_replace(
            ['{{FILES_ARRAY}}', '{{ALL_SPLIT_PDF_PATHS}}'],
            [$jsFilesJson, $allPdfItemsString],
            $template
        );

        // Buat nama file unik dan simpan
        $jsxFilename = 'modularVectorArtboards_' . now()->format('YmdHis') . '.jsx';
        $jsxFullPath = rtrim($outputPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $jsxFilename;

        file_put_contents($jsxFullPath, $jsxContent);

        return $jsxFullPath;
    }
}
