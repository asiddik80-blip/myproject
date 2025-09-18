<?php

namespace App\Data;

class Sidebar
{
    public static function getAll()
    {
        return [
            [
                'no'    => 1,
                'title' => 'Testing',
                'tombol' => [
                    [
                        'nama'  => 'PDF to AI',
                        'icon'  => 'fa fa-laptop menu-icon',
                        'link'  => 'convert-pdf',
                        'level' => '1',
                        'show'  => '0'
                    ],
                    [
                        'nama'  => 'Multiple Illustrator',
                        'icon'  => 'fa fa-laptop menu-icon',
                        'link'  => 'convert-multiple-pdf',
                        'level' => '1',
                        'show'  => '0'
                    ],
                    [
                        'nama'  => 'Macwin PDF',
                        'icon'  => 'fa fa-mac menu-icon',
                        'link'  => 'macwinpdf',
                        'level' => '1',
                        'show'  => '0'
                    ],
                    [
                        'nama'  => 'Cek Vektor PDF',
                        'icon'  => 'fa fa-mac menu-icon',
                        'link'  => 'cekvektorpdf',
                        'level' => '1',
                        'show'  => '0'
                    ],
                    [
                        'nama'  => 'Convert PDF',
                        'icon'  => 'fa fa-mac menu-icon',
                        'link'  => 'ocrpdf',
                        'level' => '1',
                        'show'  => '1'
                    ],
                ],
            ],
            [
                'no'    => 2,
                'title' => ' ',
                'tombol' => [
                    [
                        'nama'  => 'Page D Sub Class ITEM',
                        'icon'  => 'fa fa-laptop menu-icon',
                        'link'  => 'debugpaged',
                        'level' => '1',
                        'show'  => '0'
                    ],
                ],
            ],
            
        ];
    }
}
