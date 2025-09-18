<?php

namespace App\Data;

class OCRWhitelist
{
    public static function get(): array
    {
        return [
            'ACCESS', 'PANEL', 'DRAWING', 'HELP',
            'REMOVE', 'INSTALL', 'CAUTION', 'SECURE',
            'OPEN', 'CLOSE', 'CHECK', 'LINING', '2L', '22P',
            'MOTOR', 'VALVE', 'BOX', 'REAR', 'FRONT',
            'PULL', 'PUSH', 'LIGHT', 'CABLE', 'CONDUIT'
        ];
    }
}
