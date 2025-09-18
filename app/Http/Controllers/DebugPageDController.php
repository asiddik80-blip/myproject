<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Helper\ScanZoneMapHelper;
use App\Helper\ImageOverlayDrawer;
use App\Helper\PageDRegionCaptionDetector;
use App\Helper\AnchorFromVisualBoxHelper;
use App\Helper\OCRTextPlacardZone;
use App\Helper\OCRDrawingNoCleaner;
use App\Helper\OCRTSVParser;
use App\Helper\entryPartlist;
use App\Helper\ImagePreprocessorHelper;
use App\Helper\RedRegionDetectorHelper;
use App\Helper\RedBoxAndTextHelper;
use App\Helper\IsBoxInsideZone;
use App\Helper\DetectPlacardType;

class DebugPageDController extends Controller
{
    public function index()
    {
        return view('debugpaged.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'ocr_json' => 'required|file|mimes:json',
            'image_file' => 'required|image|mimes:jpg,jpeg,png',
        ]);

        // Simpan file sementara
        $ocrJsonPath = $request->file('ocr_json')->store('debug');
        $imagePath = $request->file('image_file')->store('debug');

        $ocrData = json_decode(Storage::get($ocrJsonPath), true);
        $imageFullPath = storage_path("app/{$imagePath}");

        $drawingNoPrefix = 'A511351610';

        // Deteksi caption
        $captionResults = PageDRegionCaptionDetector::detect($ocrData, $imageFullPath);

        // Deteksi zone
        $zoneJsonPath = storage_path('app/debug/scan_zones.json');
        ScanZoneMapHelper::exportZonesToJson($zoneJsonPath);
        $zones = ScanZoneMapHelper::getZonesFromImage($imageFullPath);
        $paperZone = collect($zones)->firstWhere('type', 'paper');

        // Visual box preprocessing
        $visualBoxesRaw = ImagePreprocessorHelper::runVisualPreprocessing($imageFullPath, $zoneJsonPath);
        $visualBoxesFiltered = array_filter($visualBoxesRaw, fn($box) => IsBoxInsideZone::cekBoks($box, $paperZone));
        $visualBoxesFiltered = array_values($visualBoxesFiltered);

        // Red box detection
        $redBoxesRaw = RedRegionDetectorHelper::detectRedRegions($imageFullPath);
        $redBoxesFiltered = array_filter($redBoxesRaw, fn($box) => IsBoxInsideZone::cekBoks($box, $paperZone));
        $redBoxesFiltered = array_values($redBoxesFiltered);

        // Anchor detection
        $placardZones = AnchorFromVisualBoxHelper::detect($imageFullPath, $visualBoxesFiltered, $paperZone);

        // Hilangkan duplikat anchor
        $seen = [];
        $placardZones = array_filter($placardZones, function ($pz) use (&$seen) {
            $anchor = $pz['anchorText'] ?? null;
            if (!$anchor || isset($seen[$anchor])) return false;
            $seen[$anchor] = true;
            return true;
        });
        $placardZones = array_values($placardZones);

        // Urutkan berdasarkan nomor ITEM-xxx
        usort($placardZones, function ($a, $b) {
            $extractNumber = fn($text) => preg_match('/ITEM-(\d{3})/', $text, $m) ? (int) $m[1] : 0;
            return $extractNumber($a['anchorText']) <=> $extractNumber($b['anchorText']);
        });

        // Tambahkan teks ke placard
        $placardZones = OCRTextPlacardZone::appendTextToPlacards($imageFullPath, $placardZones);

        // Bersihkan OCR + ambil noise
        $detectedNoise = [];
        foreach ($placardZones as &$pz) {
            if (!isset($pz['tsvRaw']) || !is_array($pz['tsvRaw'])) continue;

            $originalWords = $pz['tsvRaw'];
            $cleaningResult = OCRDrawingNoCleaner::cleanDrawNoWithLog($originalWords, $drawingNoPrefix);

            $pz['tsvRaw'] = $cleaningResult['cleaned'];
            $detectedNoise = array_merge($detectedNoise, $cleaningResult['noise_detected']);

            $groupedLines = OCRTSVParser::groupByLine($cleaningResult['cleaned']);
            $pz['textBody'] = implode(' ', array_column($groupedLines, 'text'));
            $pz['textLines'] = $groupedLines;
        }
        unset($pz);

        // Tandai red box & text merah
        $placardZones = RedBoxAndTextHelper::injectRedInfo($placardZones, $redBoxesFiltered);

        // Tambahkan info kode placard & partlist
        foreach ($placardZones as &$pz) {
            $result = DetectPlacardType::deteksi($pz['visualBox']);
            $pz['kode-placard'] = $result['kode-placard'];
            $pz['tipe-placard'] = $result['tipe-placard'];
            $pz['tag-placard']  = $result['tag-placard'];
            $pz['partlist']     = entryPartlist::generatePartlist($drawingNoPrefix, $pz['anchorText'] ?? '');
        }
        unset($pz);

        // Siapkan data debug
        $debugTsvRaw = [];
        foreach ($placardZones as $pz) {
            $debugTsvRaw[] = [
                'item' => $pz['anchorText'] ?? '',
                'lines' => $pz['tsvRaw'] ?? [],
            ];
        }

        $visualBoxes = array_map(fn($pz) => $pz['visualBox'], $placardZones);
        $anchors = array_map(fn($pz) => [
            'text' => $pz['anchorText'],
            'bounding_box' => $pz['anchorBox'],
        ], $placardZones);

        // Gambar overlay
        $tempOverlayPath = storage_path('app/debug/overlay.jpg');
        ImageOverlayDrawer::drawOverlay($imageFullPath, $anchors, $placardZones, $visualBoxes, $tempOverlayPath);

        if (!File::exists(public_path('debug'))) {
            File::makeDirectory(public_path('debug'), 0755, true);
        }
        File::copy($tempOverlayPath, public_path('debug/overlay.jpg'));
        File::copy($imageFullPath, public_path('debug/original.jpg'));

        $zoneOverlayPath = public_path('debug/zone_overlay.jpg');
        ImageOverlayDrawer::drawZoneOverlay($imageFullPath, $zones, $zoneOverlayPath);

        return view('debugpaged.result', [
            'captions' => $captionResults,
            'anchors' => $anchors,
            'visual_boxes' => $visualBoxes,
            'placard_zones' => $placardZones,
            'ocrJson' => $ocrData,
            'imagePath' => 'debug/original.jpg',
            'debugTsvRaw' => $debugTsvRaw,
            'zonesFromImage' => $zones,
            'detectedNoiseWords' => $detectedNoise,
            'red_boxes' => $redBoxesFiltered,
        ]);
    }
}
