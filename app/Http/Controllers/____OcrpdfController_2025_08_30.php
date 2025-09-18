<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;


use App\Helper\AnchorFromVisualBoxHelper;
use App\Helper\DetectPlacardType;
use App\Helper\DrawingNumberExtractor;
use App\Helper\entryPartlist;
use App\Helper\IllustratorInvoker;
use App\Helper\ImagePreprocessorHelper;
use App\Helper\ImagickPageHelper;
use App\Helper\IsBoxInsideZone;
use App\Helper\JsonOCRWriter;
use App\Helper\JsonOcrCleaner;
use App\Helper\JSXBuilder;
use App\Helper\NormalizePDFHelper;

use App\Helper\OCRDrawingNoCleaner;
use App\Helper\OcrPdfMetadataBuilder;
use App\Helper\OCRTextPlacardZone;
use App\Helper\OcrTextGrouper;
use App\Helper\OCRTSVParser;
use App\Helper\PageDRegionCaptionDetector;
use App\Helper\PageDSubClassifier;
use App\Helper\PageTypeClassifier;
use App\Helper\PDFUploaderHelper;
use App\Helper\RedBoxAndTextHelper;
use App\Helper\RedRegionDetectorHelper;
use App\Helper\ScanZoneMapHelper;
use App\Helper\SplitPDFHelper;
use App\Helper\ZIPExporter;

class OcrpdfController extends Controller
{
    // Menampilkan halaman upload
    public function index()
    {
        return view('ocrpdf.index');
    }

    // Menangani upload beberapa file PDF
	public function convert(Request $request)
	{
		$request->validate([
			'pdf_files'     => 'required|array|max:3',
			'pdf_files.*'   => 'mimes:pdf|max:10240',
		]);

		$tempDir = storage_path('app/temp_uploads');
		$normalizedBaseDir = storage_path('app/normalized_pdfs');
		$splitDir = storage_path('app/split_pdfs');

		// Step 1: Simpan file upload
		$uploadResult = PDFUploaderHelper::storeUploadedFiles($request->file('pdf_files'), $tempDir);

		// Bersihkan folder normalized_pdfs sebelum normalize
		if (File::exists($normalizedBaseDir)) {
			File::cleanDirectory($normalizedBaseDir);
		} else {
			File::makeDirectory($normalizedBaseDir, 0755, true);
		}

		// Step 2: Normalize PDFs terlebih dahulu
		$normalizedResults = [];
		foreach ($uploadResult['paths'] as $index => $pdfPath) {
			$originalName = pathinfo($uploadResult['original_names'][$index], PATHINFO_FILENAME);

			// Folder untuk setiap file PDF
			$normalizedDir = $normalizedBaseDir . '/' . $originalName;
			if (!file_exists($normalizedDir)) {
				mkdir($normalizedDir, 0777, true);
			}

			$normalizedPath = $normalizedDir . '/' . $originalName . '_normalized.pdf';

			NormalizePDFHelper::normalizePDFwithQPDF($pdfPath, $normalizedPath);

			$normalizedResults[] = [
				'original_name' => $originalName,
				'normalized_path' => $normalizedPath,
			];
		}

		// Step 3: Split PDF yang sudah dinormalisasi
		$splitResults = [];
		foreach ($normalizedResults as $result) {
			$splitResults[] = SplitPDFHelper::splitPdfWithQPDF($result['normalized_path'], $splitDir);
		}

		// Step 4: Simpan ke session
		$pdfSplitPaths = [];
		foreach ($splitResults as $splitResult) {
			if (isset($splitResult['split_paths'])) {
				foreach ($splitResult['split_paths'] as $p) {
					$pdfSplitPaths[] = $p;
				}
			}
		}

		session([
			'temp_pdf_paths' => $uploadResult['paths'],
			'split_pdf_paths' => array_column($splitResults, 'split_paths'),
			'normalized_pdf_paths' => array_column($normalizedResults, 'normalized_path'),
			'page_counts' => $uploadResult['page_counts'],
			'original_filenames' => $uploadResult['original_names'],
			'all_split_pdf_paths'  => $pdfSplitPaths, // tambahan variabel flat untuk dikirim ke JSX
		]);

		return redirect()->route('ocrpdf.processing');
	}


    // Menampilkan halaman Processing
    public function processing()
    {
        $pageCounts = session('page_counts', []);
		$allSplitPdfPaths = session('all_split_pdf_paths', []);

        return view('ocrpdf.processing', compact('pageCounts','allSplitPdfPaths'));
    }

   public function processHeavy(Request $request)
	{
		set_time_limit(7200);

		$storedPaths        = session('temp_pdf_paths', []);
		$originalFilenames  = session('original_filenames', []);
		$uploadPath         = storage_path('app/uploads');
		$convertedPath      = storage_path('app/converted');
		$scriptsPath        = storage_path('scripts');
		$ocrTempPath        = storage_path('app/ocrtemp');

		foreach ([$uploadPath, $convertedPath, $scriptsPath, $ocrTempPath] as $folder) {
			if (!is_dir($folder)) mkdir($folder, 0777, true);
		}

		$fileList = [];
		$fileNames = [];
		$ocrTexts = [];
		$pageCounts = [];
		$startTime = microtime(true);

		// Variabel untuk mengumpulkan placard zones dari semua file
		$allFilesPlacardZones = [];

		
		foreach ($storedPaths as $index => $pdfPath) {
			$filename = pathinfo($pdfPath, PATHINFO_FILENAME);
			
			// 1. Tentukan folder hasil split
			$splitFolder = storage_path("app/split_pdfs/{$filename}");

			// 2. Ambil semua file split di folder tersebut dan urutkan
			$splitPages = glob($splitFolder . '/' . $filename . '_page_*.pdf');
			sort($splitPages);
	
			$originalName = $originalFilenames[$index] ?? "{$filename}.pdf";
			$targetPdfPath = $uploadPath . '/' . $filename . '.pdf';
			copy($pdfPath, $targetPdfPath);

			$dimensions = ImagickPageHelper::getDimensions($targetPdfPath);
			$widthPt = $dimensions['width'];
			$heightPt = $dimensions['height'];

			$imagick = new \Imagick();
			$imagick->setResolution(400, 400);
			$imagick->readImage($targetPdfPath);

			$pages = [];
			$totalPages = min($imagick->getNumberImages(), 28);
			$pdfFilesize = round(filesize($targetPdfPath) / 1024);

			$pageTypeSummary = ['C' => 0, 'D' => 0, 'R' => 0];
			$pageTypeDetail = [];
			$drawingNoPerPage = [];
			$candidateDrawingNumbers = [];
			$revisionsFoundAt = null;
			$fileStartTime = microtime(true);

			// Placard zones per file
			$allPlacardZones = [];

			// Loop 1: OCR, klasifikasi, simpan JSON & JPEG
			for ($i = 0; $i < $totalPages; $i++) {
				$pageNumber = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
				$imagick->setIteratorIndex($i);
				$imagick->setImageFormat('jpeg');
				$imagePath = "$ocrTempPath/{$filename}_page_{$pageNumber}.jpg";
				$imagick->writeImage($imagePath);
				$pages[] = str_replace('\\', '/', $imagePath);

				$tsvPath = "$ocrTempPath/{$filename}_page_{$pageNumber}.tsv";
				shell_exec("tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg("$ocrTempPath/{$filename}_page_{$pageNumber}") . " -l eng --oem 1 --psm 11 tsv");

				$ocrData = OCRTSVParser::parse($tsvPath);
				$ocrText = OCRTSVParser::getPlainText($ocrData);

				$drawingResult = DrawingNumberExtractor::extract($ocrText);
				$drawingNoPerPage[$pageNumber] = $drawingResult['perPageCount'];
				foreach ($drawingResult['candidates'] as $prefix => $count) {
					$candidateDrawingNumbers[$prefix] = ($candidateDrawingNumbers[$prefix] ?? 0) + $count;
				}

				arsort($candidateDrawingNumbers);
				$finalDrawingNo = array_key_first($candidateDrawingNumbers);

				// Update drawingNo di file JSON yang sudah ada (kalau ada)
				foreach ($pageTypeDetail as $pageNum => $pgInfo) {
					$jsonPath = "$uploadPath/{$filename}_page_{$pageNum}.json";
					if (file_exists($jsonPath)) {
						JsonOCRWriter::updateDrawingNo($jsonPath, $finalDrawingNo);
					}
				}

				$pageType = PageTypeClassifier::classify($ocrText, $i, $revisionsFoundAt);
				$pageTypeSummary[$pageType]++;

				// Simpan info tipe halaman & paths untuk prosesPageDItem nanti
				$subtype = null;
				if ($pageType === 'D') {
					$subtype = PageDSubClassifier::detect($ocrText, $ocrData);
				}

				$pageTypeDetail[$pageNumber] = [
					'type' => $pageType,
					'subtype' => $subtype,
					'jsonPath' => "$uploadPath/{$filename}_page_{$pageNumber}.json",
					'imagePath' => $imagePath,
					'finalDrawingNo' => $finalDrawingNo,
				];

				$groupedLines = OcrTextGrouper::groupWordsIntoLines($ocrData);

				$jsonPath = $pageTypeDetail[$pageNumber]['jsonPath'];
				JsonOCRWriter::savePageJson($jsonPath, [
					'filename' => basename($imagePath),
					'metadata' => [
						'original_filename' => $originalName,
						'stored_filename' => "$filename.pdf",
						'page_type' => $pageType,
						'drawing-no' => '',
						'timestamp' => now()->format('Y-m-d H:i:s'),
						'page_number' => $pageNumber,
						'total_pages' => $totalPages,
						'pdf_filesize_kb' => $pdfFilesize,
						'pdf_dimensions' => "$widthPt x $heightPt pt",
						'ocr_engine' => 'Tesseract v5.3.1 (TSV)'
					],
					'text' => $ocrText,
					'words' => $ocrData,
					'lines' => $groupedLines,
				]);

				JsonOcrCleaner::cleanEmptyLines($jsonPath);
				file_put_contents("$uploadPath/{$filename}_page_{$pageNumber}.txt", $ocrText);
				$ocrTexts["{$filename}_page_{$pageNumber}"] = $ocrText;
			}

			// Loop 2: Proses Page D Item setelah semua JSON & JPEG sudah pasti ada
			foreach ($pageTypeDetail as $pageNumber => $info) {
				if ($info['type'] === 'D' && ($info['subtype'] ?? '') === 'I') {
					$processPageDResult = $this->processPageDItem(
						$info['jsonPath'],
						$info['imagePath'],
						$info['finalDrawingNo'],
						$filename,
						$pageNumber
					);
					$pageTypeDetail[$pageNumber]['page_d_item'] = $processPageDResult;

					// Gabungkan placard zones halaman ini ke variabel global per file
					$allPlacardZones = array_merge($allPlacardZones, $processPageDResult['placardZones']);
				}
			}

			OcrPdfMetadataBuilder::generate(
				$uploadPath,
				$filename,
				$originalName,
				$totalPages,
				$pdfFilesize,
				"$widthPt x $heightPt pt",
				$finalDrawingNo,
				$drawingNoPerPage,
				$pageTypeSummary,
				$pageTypeDetail,
				microtime(true) - $fileStartTime,
				$pages
			);

			$fileList[] = [
				'input' => $splitPages, // array tiap halaman split
				'output' => str_replace('\\', '/', "$convertedPath/{$filename}.ai"),
				'pages' => $pages,
				'totalPages' => count($splitPages),
				'width' => 841.68,
				'height' => 594.72,
			];

			$fileNames[] = $filename;
			$pageCounts[$filename] = $totalPages;

			$imagick->clear();
			$imagick->destroy();

			// Gabungkan placard zones file ini ke variabel global seluruh file
			$allFilesPlacardZones = array_merge($allFilesPlacardZones, $allPlacardZones);
		}

		// Simpan placard zones gabungan semua file ke session
		session(['placard_zones_all' => $allFilesPlacardZones]);

		$jsxFile = JSXBuilder::build($fileList, resource_path('scripts/modularVectorArtboards.jsx'), $scriptsPath);
		IllustratorInvoker::run($jsxFile, count($fileNames));

		// Debug isi array penting
		\Log::info('Debug processHeavy: converted_file_names = ', $fileNames);
		\Log::info('Debug processHeavy: page_counts = ', $pageCounts);
		\Log::info('Debug processHeavy: total placard zones count = ' . count($allFilesPlacardZones));
		\Log::info('Debug processHeavy: first 3 placard zones = ', array_slice($allFilesPlacardZones, 0, 3));
		\Log::info('Debug processHeavy: jsxFile path = ' . $jsxFile);

		session([
			'converted_file_names' => $fileNames,
			'conversion_duration' => round(microtime(true) - $startTime, 2),
			'ocr_texts' => $ocrTexts,
			'pdf_count' => count($fileNames),
			'page_counts' => $pageCounts,
		]);

		// Debug isi session langsung
		\Log::info('Session converted_file_names:', session('converted_file_names'));
		\Log::info('Session pdf_count:', session('pdf_count'));
		\Log::info('Session page_counts:', session('page_counts'));
		\Log::info('Session placard_zones_all count:', count(session('placard_zones_all') ?? []));

		return response()->json(['status' => 'done']);
	}

    /**
     * Fungsi modular untuk proses Page D Sub Class I (processPageDItem)
     */
    protected function processPageDItem(string $ocrJsonPath, string $imageFullPath, string $drawingNoPrefix = '', string $filename = '', string $pageNumber = '')
	{
		$ocrData = json_decode(file_get_contents($ocrJsonPath), true);

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

			// ** Tambahkan info file dan page untuk kemudahan grouping di view **
			$pz['file'] = $filename;
			$pz['page'] = $pageNumber;
		}
		unset($pz);

		return [
			'placardZones' => $placardZones,
			'detectedNoise' => $detectedNoise,
			'captionResults' => $captionResults,
			'redBoxesFiltered' => $redBoxesFiltered,
			'zones' => $zones,
		];
	}

    // Tampilkan halaman hasil
    public function result()
    {
        return view('ocrpdf.result', [
            'fileNames' => session('converted_file_names', []),
            'duration' => session('conversion_duration', 0),
            'ocrTexts' => session('ocr_texts', []),
            'pdfCount' => session('pdf_count', 0),
            'pageCounts' => session('page_counts', []),
			'placardZones' => session('placard_zones_all', []), // <-- tambahan

        ]);
    }

    // Unduh file zip hasil konversi
    public function downloadZip()
    {
        $fileNames = session('converted_file_names', []);
        $zipPath = storage_path('app/converted_illustrator_files_' . now()->format('Ymd_His') . '.zip');

        $zipPath = ZIPExporter::createZip($fileNames, storage_path('app/converted'), $zipPath);
        return ZIPExporter::downloadAndDelete($zipPath);
    }
}
