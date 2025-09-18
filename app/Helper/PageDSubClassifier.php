<?php

namespace App\Helper;

class PageDSubClassifier
{
    /**
     * Mendeteksi sub-tipe halaman Page Type D, berdasarkan teks OCR.
     * Saat ini hanya mendeteksi pola "ITEM-xxx" sebagai tipe "I".
     *
     * @param string $ocrText Teks OCR dari halaman
     * @return string|null    Sub-tipe halaman D: 'I', 'SC1', 'SC2', atau null jika tidak dikenali
     */
    public static function detect(string $ocrText): ?string
    {
        // Bersihkan dan pecah per baris
        $lines = preg_split("/\r\n|\n|\r/", $ocrText);

        foreach ($lines as $line) {
            // Cek apakah ada kata ITEM-xxx (xxx = angka atau huruf)
            if (preg_match('/ITEM[-\s]?[A-Z0-9]{2,}/i', $line)) {
                return 'I'; // Tipe 'I' jika ditemukan pola ITEM
            }
        }

        // Tambahan untuk masa depan: bisa tambahkan pola SC1, SC2 di sini

        return null;
    }

    /**
     * Menambahkan hasil sub-klasifikasi ke struktur page_type_detail yang sudah ada.
     *
     * @param array $pageTypeDetail       Data page_type_detail dari hasil metadata
     * @param array $ocrTextsPerPage      Teks OCR per halaman dalam format [page_number => ocrText]
     * @return array                      Hasil array page_type_detail dengan nested sub_type
     */
    public static function classifyPages(array $pageTypeDetail, array $ocrTextsPerPage): array
    {
        $result = [];

        foreach ($pageTypeDetail as $pageNum => $pageType) {
            if ($pageType === 'D') {
                $ocrText = $ocrTextsPerPage[$pageNum] ?? '';
                $subType = self::detect($ocrText);

                $result[$pageNum] = [
                    'type' => 'D',
                    'sub_type' => $subType ?? 'UNKNOWN',
                ];
            } else {
                $result[$pageNum] = $pageType;
            }
        }

        return $result;
    }
}
