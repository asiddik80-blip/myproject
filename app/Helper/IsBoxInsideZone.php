<?php

namespace App\Helper;

class IsBoxInsideZone
{
    /**
     * Mengecek apakah sebuah box berada di dalam zone tertentu.
     *
     * @param array $box  [x, y, width, height]
     * @param array $zone [x, y, width, height]
     * @return bool
     */
    public static function cekBoks(array $box, array $zone): bool
    {
        $boxRight = $box['x'] + $box['width'];
        $boxBottom = $box['y'] + $box['height'];
        $zoneRight = $zone['x'] + $zone['width'];
        $zoneBottom = $zone['y'] + $zone['height'];

        return $box['x'] >= $zone['x'] &&
               $box['y'] >= $zone['y'] &&
               $boxRight <= $zoneRight &&
               $boxBottom <= $zoneBottom;
    }
}