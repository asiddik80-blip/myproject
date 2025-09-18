<?php

namespace App\Helper;

use Imagick;

class ImagickPageHelper
{
    public static function getPageCount(string $pdfPath): int
    {
        try {
            $imagick = new Imagick();
            $imagick->pingImage($pdfPath);
            return $imagick->getNumberImages();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public static function getDimensions(string $pdfPath): array
    {
        try {
            $imagick = new Imagick();
            $imagick->readImage($pdfPath);
            $geometry = $imagick->getImageGeometry();
            return [
                'width' => $geometry['width'],
                'height' => $geometry['height']
            ];
        } catch (\Exception $e) {
            return [
                'width' => 0,
                'height' => 0
            ];
        }
    }
}
