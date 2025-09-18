<?php

namespace App\Helper;

class TextCleaner
{
    public static function clean(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^[\s_\-\*\.\—]+/', '', $text);  // depan
        $text = preg_replace('/[\s_\-\*\.\—]+$/', '', $text);  // belakang
        $text = preg_replace('/\s+/', ' ', $text);             // spasi ganda → 1
        return trim($text);
    }
}
