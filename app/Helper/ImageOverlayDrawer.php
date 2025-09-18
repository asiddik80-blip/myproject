<?php

namespace App\Helper;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class ImageOverlayDrawer
{
    private static array $config = [
        'anchor' => [
            'stroke' => '#0f45f7',       // biru
            'label_fill' => '#0f45f7',
        ],
        'placard' => [
            'stroke' => '#ff8503',       // oranye
            'label_fill' => '#ff8503',
        ],
        'visual' => [
            'stroke' => '#ff002f',       // merah
            'label_fill' => '#ff002f',
        ],
    ];

    /**
     * Gambar overlay utama: visual boxes, anchor, placard
     */
    public static function drawOverlay(
        string $imagePath,
        array $anchors,         // ← sudah dari AnchorFromVisualBoxHelper
        array $placards,
        array $visualBoxes,
        string $outputPath
    ): void {
        $img = new Imagick($imagePath);
        $draw = new ImagickDraw();

        $draw->setFontSize(50);
        $draw->setFontWeight(600);
        $draw->setStrokeWidth(2);
        $draw->setFillOpacity(0); // default transparan

        // 🔶 Visual Boxes
        foreach ($visualBoxes as $i => $box) {
            $x = $box['x'];
            $y = $box['y'];
            $w = $box['width'];
            $h = $box['height'];

            $draw->setStrokeColor(new ImagickPixel(self::$config['visual']['stroke']));
            $draw->setStrokeOpacity(1);
            $draw->setFillOpacity(0);
            $draw->rectangle($x, $y, $x + $w, $y + $h);

            $draw->setFillColor(new ImagickPixel(self::$config['visual']['label_fill']));
            $draw->setFillOpacity(1);
            $draw->annotation($x, $y - 8, "VB" . ($i + 1));
            $draw->setFillOpacity(0);
        }

        // 🔵 Anchors (captionResults)
        foreach ($anchors as $anchor) {
            $bbox = $anchor['bounding_box'] ?? null;

            $x = $bbox['x'] ?? $anchor['x'] ?? 0;
            $y = $bbox['y'] ?? $anchor['y'] ?? 0;
            $w = $bbox['width'] ?? $anchor['width'] ?? 0;
            $h = $bbox['height'] ?? $anchor['height'] ?? 0;

            $draw->setStrokeColor(new ImagickPixel(self::$config['anchor']['stroke']));
            $draw->setStrokeOpacity(1);
            $draw->setFillOpacity(0);
            $draw->rectangle($x, $y, $x + $w, $y + $h);

            $label = "Anchor {$anchor['text']}";
            $draw->setFillColor(new ImagickPixel(self::$config['anchor']['label_fill']));
            $draw->setFillOpacity(1);
            $draw->annotation($x, $y - 10, $label);
            $draw->setFillOpacity(0);
        }

        // 🔴 Placard Zones
        
    foreach ($placards as $placard) {
        if (!isset($placard['visualBox'])) continue;

        $z = $placard['visualBox'];
        $x = $z['x'];
        $y = $z['y'];
        $w = $z['width'];
        $h = $z['height'];

        $draw->setStrokeColor(new ImagickPixel(self::$config['placard']['stroke']));
        $draw->setStrokeOpacity(1);
        $draw->setFillOpacity(0);
        $draw->rectangle($x, $y, $x + $w, $y + $h);

       
        // Tambahkan tipe placard di kanan atas VB
        $tipe = $placard['tipe-placard'] ?? null;
        $tipeText = $tipe ?: 'Belum terdefinisi';

        // Set warna teks
        $draw->setFillColor(new ImagickPixel(self::$config['placard']['label_fill']));
        $draw->setFillOpacity(1); // Aktifkan isi teks agar terlihat
        $draw->annotation($x + 150, $y - 10, $tipeText);

        // Kembalikan transparansi agar bentuk kotak tetap kosong
        $draw->setFillOpacity(0);

    }


        $img->drawImage($draw);
        $img->writeImage($outputPath);
        $img->clear();
        $img->destroy();
    }

    /**
     * Gambar overlay zona (paper, milimeter, dsb)
     */
    public static function drawZoneOverlay(string $imagePath, array $zones, string $outputPath): void
    {
        $img = new Imagick($imagePath);
        $draw = new ImagickDraw();
        $draw->setFontSize(24);
        $draw->setStrokeWidth(4);
        $draw->setFillOpacity(0);

        foreach ($zones as $zone) {
            $x = $zone['x'];
            $y = $zone['y'];
            $w = $zone['width'];
            $h = $zone['height'];
            $name = strtoupper($zone['name']);
            $type = strtoupper($zone['type']);

            $draw->setStrokeColor(new ImagickPixel('#00aa00'));
            $draw->rectangle($x, $y, $x + $w, $y + $h);

            $draw->setFillColor(new ImagickPixel('#00aa00'));
            $draw->setFillOpacity(1);
            $draw->annotation($x, $y - 10, "{$name} ({$type})");
            $draw->setFillOpacity(0);
        }

        $img->drawImage($draw);
        $img->writeImage($outputPath);
        $img->clear();
        $img->destroy();
    }
}
