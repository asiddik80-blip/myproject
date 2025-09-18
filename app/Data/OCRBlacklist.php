<?php

namespace App\Data;

class OCRBlacklist
{
    /**
     * Karakter satuan yang dianggap noise fatal jika muncul sebagai hasil OCR
     */
    public static function get(): array
    {
        return ['7', ':', '-', '|', ';'];
    }
}
