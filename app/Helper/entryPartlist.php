<?php

namespace App\Helper;

class entryPartlist
{
    /**
     * Menghasilkan partlist dari prefix + anchor
     */
    public static function generatePartlist(string $prefix, string $anchorText): ?string
    {
        if (preg_match('/ITEM-(\d{3})/', $anchorText, $matches)) {
            return $prefix . '-' . $matches[1];
        }

        return null;
    }
}
