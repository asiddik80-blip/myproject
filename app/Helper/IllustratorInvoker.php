<?php

namespace App\Helper;

class IllustratorInvoker
{
    /**
     * Menjalankan file batch untuk memanggil Illustrator dengan file JSX
     *
     * @param string $jsxPath Path file JSX
     * @param int $fileCount Jumlah file yang akan diproses (untuk parameter tambahan)
     * @param string|null $batchScript Path file batch, default ke lokasi umum
     * @return void
     */
    public static function run(string $jsxPath, int $fileCount, ?string $batchScript = null): void
    {
        if (empty($batchScript)) {
            $batchScript = base_path('scripts/run_illustrator_ocrpdf.bat');
        }

        if (!file_exists($batchScript)) {
            throw new \Exception("Batch script not found: {$batchScript}");
        }

        $cmd = "cmd /C \"\"{$batchScript}\" \"{$jsxPath}\" {$fileCount}\"";
        shell_exec($cmd);
    }
}
