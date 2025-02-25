<?php

namespace App\Data;

class sidebar
{
    public static function getAll()
    {
        return [
            [
                'no'    => 1,
                'title' => 'KBIH',
                'tombol' => [
                    [
                        'nama'  => 'Ayman',
                        'icon'  => 'mdi mdi-apps menu-icon',
                        'link'  => 'dashboard',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Profil',
                        'icon'  => 'mdi mdi-apps menu-icon',
                        'link'  => 'profilKBIH',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Pemberangkatan',
                        'icon'  => 'mdi mdi-apps menu-icon',
                        'link'  => 'pemberangkatan',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Formasi',
                        'icon'  => 'mdi mdi-apps menu-icon',
                        'link'  => 'formasi',
                        'level' => '1'
                    ],
                ],
            ],
            [
                'no'    => 2,
                'title' => 'User',
                'tombol' => [
                    [
                        'nama'  => 'Jamaah',
                        'icon'  => 'mdi mdi-floor-plan menu-icon',
                        'link'  => 'jamaah',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Manasik',
                        'icon'  => 'mdi mdi-floor-plan menu-icon',
                        'link'  => 'manasik',
                        'level' => '1'
                    ],
                ],
            ],
            [
                'no'    => 3,
                'title' => 'Akomodasi',
                'tombol' => [
                    [
                        'nama'  => 'Pesawat',
                        'icon'  => 'mdi mdi-account-circle-outline menu-icon',
                        'link'  => 'pesawat',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Hotel',
                        'icon'  => 'mdi mdi-table menu-icon',
                        'link'  => 'hotel',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Bus',
                        'icon'  => 'mdi mdi-chart-line menu-icon',
                        'link'  => 'bus',
                        'level'  => '1'
                    ],
                    [
                        'nama'  => 'Kereta Api',
                        'icon'  => 'mdi mdi-layers-outline menu-icon',
                        'link'  => 'kereta',
                        'level'  => '2'
                    ],
                    [
                        'nama'  => 'Logistik',
                        'icon'  => 'mdi mdi-layers-outline menu-icon',
                        'link'  => 'logistik',
                        'level'  => '2'
                    ],
                ],
            ],
            [
                'no'    => 4,
                'title' => 'Dokumentasi',
                'tombol' => [
                    [
                        'nama'  => 'Upload PDF',
                        'icon'  => 'mdi mdi-card-text-outline menu-icon',
                        'link'  => 'uploadpdf',
                        'level'  => '1'
                    ],
                ],
            ],
        ];
    }
}
