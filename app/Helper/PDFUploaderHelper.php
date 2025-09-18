<?php

namespace App\Helper;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Imagick;

class PDFUploaderHelper
{
    public static function storeUploadedFiles(array $uploadedFiles, string $destination): array
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $storedPaths = [];
        $pageCounts = [];
        $originalNames = [];

        foreach ($uploadedFiles as $file) {
            if (!$file instanceof UploadedFile) continue;

            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '-' . now()->format('YmdHis') . '-' . Str::random(4) . '.pdf';

            $movedFile = $file->move($destination, $filename);
            $fullPath = $movedFile->getPathname();
            $storedPaths[] = $fullPath;
            $originalNames[] = $file->getClientOriginalName();

            try {
                $imagick = new Imagick();
                $imagick->pingImage($fullPath);
                $pageCounts[$filename] = $imagick->getNumberImages();
            } catch (\Exception $e) {
                $pageCounts[$filename] = 0;
            }
        }

        return [
            'paths' => $storedPaths,
            'page_counts' => $pageCounts,
            'original_names' => $originalNames,
        ];
    }
}
