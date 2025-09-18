<?php

namespace App\Helper;

class PageTypeClassifier
{
    /**
     * Mengklasifikasikan halaman menjadi 'C', 'D', atau 'R'
     *
     * @param string $ocrText
     * @param int $pageIndex
     * @param int|null &$revisionsFoundAt  (akan diisi jika belum ditemukan)
     * @return string
     */
    public static function classify(string $ocrText, int $pageIndex, ?int &$revisionsFoundAt): string
    {
        if ($pageIndex === 0) {
            return 'C';
        }

        if (stripos($ocrText, 'Revisions') !== false && $revisionsFoundAt === null) {
            $revisionsFoundAt = $pageIndex;
        }

        if ($revisionsFoundAt !== null && $pageIndex >= $revisionsFoundAt) {
            return 'R';
        }

        return 'D';
    }
}
