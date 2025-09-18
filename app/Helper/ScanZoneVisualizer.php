<?php

namespace App\Helper;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Log;

class ScanZoneVisualizer
{
    public static function draw(string $imagePath, string $outputPath, array $zones): void
    {
        $img = Image::make($imagePath);

        foreach ($zones as $zone) {
            $x = $zone['x'];
            $y = $zone['y'];
            $w = $zone['width'];
            $h = $zone['height'];

            $color = match ($zone['type']) {
                'paper' => '#0066cc',       // Biru
                'milimeter' => '#cc0000',   // Merah
                default => '#999999',
            };

            // Kotak overlay
            $img->rectangle($x, $y, $x + $w, $y + $h, function ($draw) use ($color) {
                $draw->border(4, $color);
            });

            // Label teks
            $label = "{$zone['name']}";
            $img->text($label, $x + 5, $y - 5, function ($font) use ($color) {
                $font->size(26);
                $font->color($color);
                $font->file(public_path('fonts/arial.ttf')); // Pastikan font tersedia
            });
        }

        $img->save($outputPath);
    }
}
