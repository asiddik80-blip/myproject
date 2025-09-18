<?php

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class PageDPlacardZoneItemDetector
{
    public static function detect(array $ocrData, string $imagePath): array
    {
        $anchors = [];

        // Ambil semua anchor berbasis teks ITEM-xxx dari words[]
        foreach ($ocrData['words'] ?? [] as $word) {
            if (isset($word['text']) && preg_match('/ITEM-\d{3}/', $word['text'])) {
                $anchors[] = [
                    'text' => $word['text'],
                    'x' => $word['bbox']['left'],
                    'y' => $word['bbox']['top'],
                    'width' => $word['bbox']['width'],
                    'height' => $word['bbox']['height'],
                ];
            }
        }

        $visualBoxes = self::getVisualBoxes($imagePath);
        $placardZones = [];

        foreach ($anchors as $anchor) {
            $anchorCenterX = $anchor['x'] + $anchor['width'] / 2;
            $anchorY = $anchor['y'];

            // Cari kandidat visual box yang letaknya di atas anchor dan sejalur horizontal
            $candidates = array_filter($visualBoxes, function ($box) use ($anchorY, $anchorCenterX, $anchor) {
                $boxBottomY = $box['y'] + $box['height'];
                $boxCenterX = $box['x'] + $box['width'] / 2;
                return ($boxBottomY < $anchorY - 10) &&
                       (abs($boxCenterX - $anchorCenterX) < max(200, $anchor['width'] * 1.5));
            });

            if (!empty($candidates)) {
                // Urutkan berdasarkan jarak vertikal terdekat dengan anchor
                usort($candidates, function ($a, $b) use ($anchorY) {
                    return ($anchorY - ($a['y'] + $a['height'])) - ($anchorY - ($b['y'] + $b['height']));
                });

                $bestBox = $candidates[0];

                // Perbesar buffer zone ±50px
                $zone = [
                    'x' => max(0, $bestBox['x'] - 50),
                    'y' => max(0, $bestBox['y'] - 50),
                    'width' => $bestBox['width'] + 100,
                    'height' => $bestBox['height'] + 100,
                ];

                // Cari semua word yang masuk ke dalam zona ini
                $zoneWords = [];
                foreach ($ocrData['words'] ?? [] as $word) {
                    if (!isset($word['bbox']) || empty(trim($word['text'] ?? ''))) continue;
                    $wx = $word['bbox']['left'];
                    $wy = $word['bbox']['top'];

                    if (
                        $wx >= $zone['x'] &&
                        $wy >= $zone['y'] &&
                        $wx <= $zone['x'] + $zone['width'] &&
                        $wy <= $zone['y'] + $zone['height']
                    ) {
                        $zoneWords[] = $word;
                    }
                }

                // Logging untuk debugging
                Log::info("Anchor {$anchor['text']} matched box at x={$bestBox['x']}, y={$bestBox['y']}, w={$bestBox['width']}, h={$bestBox['height']}");
                Log::info("Placard zone for {$anchor['text']} has ".count($zoneWords)." words");
                Log::info("Zone: x={$zone['x']}, y={$zone['y']}, w={$zone['width']}, h={$zone['height']}");

                // Urutkan kata-kata di dalam zona berdasarkan posisi
                usort($zoneWords, function ($a, $b) {
                    $dy = $a['bbox']['top'] - $b['bbox']['top'];
                    return abs($dy) > 10 ? $dy : ($a['bbox']['left'] - $b['bbox']['left']);
                });

                $bodyLines = array_map(fn($w) => $w['text'], $zoneWords);
                $fullBodyText = implode(' ', $bodyLines);

                $placardZones[] = [
                    'anchor' => $anchor,
                    'zone_box' => $zone,
                    'body' => $bodyLines,
                    'fullbody' => [
                        'text' => $fullBodyText,
                        'bbox' => $zone,
                    ]
                ];
            }
        }

        return [
            'placards' => $placardZones,
            'anchors' => $anchors,
            'visual_boxes' => $visualBoxes,
        ];
    }

    private static function getVisualBoxes(string $imagePath): array
    {
        $pythonPath = 'python';
        $scriptPath = base_path('app/Script/Python/detect_boxes.py');
        $escapedImagePath = escapeshellarg($imagePath);
        $command = "$pythonPath $scriptPath $escapedImagePath";
        $output = shell_exec($command);

        if (!$output) return [];
        $boxes = json_decode($output, true);
        return is_array($boxes) ? $boxes : [];
    }
}
