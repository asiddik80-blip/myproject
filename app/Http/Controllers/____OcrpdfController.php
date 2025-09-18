<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Imagick;
use ZipArchive;
use Illuminate\Support\Facades\Response;
use App\Helper\OcrTextGrouper;
use App\Helper\OcrPdfMetadataBuilder;
use App\Helper\JsonOcrCleaner; // Tambahan: untuk filter spasi kosong di JSON

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
            'pdf_files' => 'required|array|max:10',
            'pdf_files.*' => 'mimes:pdf|max:10240',
        ]);

        $tempDir = storage_path('app/temp_uploads');
        if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

        $storedPaths = [];
        $pageCounts = [];

        foreach ($request->file('pdf_files') as $file) {
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                . '-' . now()->format('YmdHis') . '-' . Str::random(4) . '.pdf';

            $path = $file->move($tempDir, $filename);
            $storedPaths[] = $path->getPathname();

            try {
                $imagick = new Imagick();
                $imagick->pingImage($path);
                $pageCounts[$filename] = $imagick->getNumberImages();
            } catch (\Exception $e) {
                $pageCounts[$filename] = 0;
            }
        }

        session([
            'temp_pdf_paths' => $storedPaths,
            'page_counts' => $pageCounts,
            'original_filenames' => array_map(fn($f) => $f->getClientOriginalName(), $request->file('pdf_files')),
        ]);

        return redirect()->route('ocrpdf.processing');
    }

    // Menampilkan halaman "processing"
    public function processing()
    {
        $pageCounts = session('page_counts', []);
        return view('ocrpdf.processing', compact('pageCounts'));
    }

    // Proses berat: OCR dengan bounding box + metadata
    public function processHeavy(Request $request)
    {
        set_time_limit(7200);

        $storedPaths = session('temp_pdf_paths', []);
        $originalFilenames = session('original_filenames', []);
        $uploadPath = storage_path('app/uploads');
        $convertedPath = storage_path('app/converted');
        $scriptsPath = storage_path('scripts');
        $ocrTempPath = storage_path('app/ocrtemp');

        foreach ([$uploadPath, $convertedPath, $scriptsPath, $ocrTempPath] as $folder) {
            if (!is_dir($folder)) mkdir($folder, 0777, true);
        }

        $fileList = [];
        $fileNames = [];
        $ocrTexts = [];
        $pageCounts = [];
        $startTime = microtime(true);

        foreach ($storedPaths as $index => $pdfPath) {
            $filename = pathinfo($pdfPath, PATHINFO_FILENAME);
            $originalName = $originalFilenames[$index] ?? "{$filename}.pdf";
            $targetPdfPath = $uploadPath . '/' . $filename . '.pdf';
            copy($pdfPath, $targetPdfPath);

            $imagick = new Imagick();
            $imagick->setResolution(300, 300);
            $imagick->readImage($targetPdfPath);

            $pages = [];
            $totalPages = min($imagick->getNumberImages(), 50);
            $pdfFilesize = round(filesize($targetPdfPath) / 1024);
            $geometry = $imagick->getImageGeometry();
            $widthPt = $geometry['width'];
            $heightPt = $geometry['height'];

            $pageTypeSummary = ['C' => 0, 'D' => 0, 'R' => 0];
            $pageTypeDetail = [];
            $drawingNoPerPage = [];
            $candidateDrawingNumbers = [];
            $revisionsFoundAt = null;
            $fileStartTime = microtime(true);

            for ($i = 0; $i < $totalPages; $i++) {
                $pageNumber = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                $imagick->setIteratorIndex($i);
                $imagick->setImageFormat('jpeg');
                $imagePath = $ocrTempPath . "/{$filename}_page_{$pageNumber}.jpg";
                $imagick->writeImage($imagePath);
                $pages[] = str_replace('\\', '/', $imagePath);

                $tsvPath = $ocrTempPath . "/{$filename}_page_{$pageNumber}.tsv";
                shell_exec("tesseract " . escapeshellarg($imagePath) . " " . escapeshellarg($ocrTempPath . "/{$filename}_page_{$pageNumber}") . " -l eng tsv");

                $ocrText = '';
                $ocrData = [];

                if (file_exists($tsvPath)) {
                    $lines = file($tsvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    $headers = str_getcsv(array_shift($lines), "\t");

                    foreach ($lines as $line) {
                        $fields = str_getcsv($line, "\t");
                        $entry = array_combine($headers, $fields);
                        if (!empty($entry['text']) && intval($entry['conf']) > 30) {
                            $ocrText .= $entry['text'] . ' ';
                            $ocrData[] = [
                                'text' => $entry['text'],
                                'conf' => (int) $entry['conf'],
                                'bbox' => [
                                    'left' => (int) $entry['left'],
                                    'top' => (int) $entry['top'],
                                    'width' => (int) $entry['width'],
                                    'height' => (int) $entry['height'],
                                ]
                            ];
                        }
                    }
                    $ocrText = trim($ocrText);
                }

                $pageType = ($i === 0) ? 'C'
                    : (($revisionsFoundAt !== null && $i >= $revisionsFoundAt) ? 'R'
                    : ((stripos($ocrText, 'Revisions') !== false && $revisionsFoundAt === null) ? ($revisionsFoundAt = $i) ? 'R' : 'D' : 'D'));

                $pageTypeSummary[$pageType]++;
                $pageTypeDetail[$pageNumber] = $pageType;

                preg_match_all('/\b[A-Z]{1}[0-9]{9}(?:-[0-9]{3})?\b/', $ocrText, $matchesAlpha);
                preg_match_all('/\b[0-9]{10}(?:-[0-9]{3})?\b/', $ocrText, $matchesNumeric);
                $foundDrawingNos = array_merge($matchesAlpha[0], $matchesNumeric[0]);

                $drawingNoPerPage[$pageNumber] = count($foundDrawingNos);
                foreach ($foundDrawingNos as $d) {
                    $prefix = explode('-', $d)[0];
                    $candidateDrawingNumbers[$prefix] = ($candidateDrawingNumbers[$prefix] ?? 0) + 1;
                }

                // Jalankan helper OcrTextGrouper untuk membuat baris dari kata-kata
                $groupedLines = OcrTextGrouper::groupWordsIntoLines($ocrData);

                // Simpan JSON awal hasil OCR
                $jsonPath = $uploadPath . "/{$filename}_page_{$pageNumber}.json";
                file_put_contents($jsonPath, json_encode([
                    'filename' => basename($imagePath),
                    'metadata' => [
                        'original_filename' => $originalName,
                        'stored_filename' => "{$filename}.pdf",
                        'page_type' => $pageType,
                        'drawing-no' => '',
                        'timestamp' => now()->format('Y-m-d H:i:s'),
                        'page_number' => $pageNumber,
                        'total_pages' => $totalPages,
                        'pdf_filesize_kb' => $pdfFilesize,
                        'pdf_dimensions' => "{$widthPt} x {$heightPt} pt",
                        'ocr_engine' => 'Tesseract v5.3.1 (TSV)',
                    ],
                    'text' => $ocrText,
                    'words' => $ocrData,
                    'lines' => $groupedLines,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // 🧹 Tambahan: bersihkan baris kosong/spasi di array 'lines'
                JsonOcrCleaner::cleanEmptyLines($jsonPath);

                // Simpan juga ke dalam file .txt
                file_put_contents($uploadPath . "/{$filename}_page_{$pageNumber}.txt", $ocrText);
                $ocrTexts["{$filename}_page_{$pageNumber}"] = $ocrText;
            }

            arsort($candidateDrawingNumbers);
            $finalDrawingNo = array_key_first($candidateDrawingNumbers);

            foreach ($pageTypeDetail as $pageNum => $pgType) {
                $jsonPath = $uploadPath . "/{$filename}_page_{$pageNum}.json";
                if (file_exists($jsonPath)) {
                    $json = json_decode(file_get_contents($jsonPath), true);
                    $json['metadata']['drawing-no'] = $finalDrawingNo;
                    file_put_contents($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }

            // Simpan metadata per file PDF
            OcrPdfMetadataBuilder::generate(
                $uploadPath,
                $filename,
                $originalName,
                $totalPages,
                $pdfFilesize,
                "{$widthPt} x {$heightPt} pt",
                $finalDrawingNo,
                $drawingNoPerPage,
                $pageTypeSummary,
                $pageTypeDetail,
                microtime(true) - $fileStartTime,
                $pages
            );

            $fileList[] = [
                'input' => str_replace('\\', '/', $targetPdfPath),
                'output' => str_replace('\\', '/', $convertedPath . '/' . $filename . '.ai'),
                'pages' => $pages,
                'width' => 841.68,
                'height' => 594.72,
            ];

            $fileNames[] = $filename;
            $pageCounts[$filename] = $totalPages;

            $imagick->clear();
            $imagick->destroy();
        }

        $template = file_get_contents(resource_path('scripts/convert_ocrpdf_artboards.jsx'));
        $jsArrayContent = collect($fileList)->map(function ($f) {
            $pagesArray = collect($f['pages'])->map(fn($p) => '"' . $p . '"')->implode(', ');
            return <<<JS
{
    input: "{$f['input']}",
    output: "{$f['output']}",
    pages: [{$pagesArray}],
    width: {$f['width']},
    height: {$f['height']}
}
JS;
        })->implode(",\n");

        $jsxContent = str_replace('{{FILES_ARRAY}}', "[{$jsArrayContent}]", $template);
        $jsxFilename = 'convert_ocrpdf_artboards_' . now()->format('YmdHis') . '.jsx';
        $jsxFile = $scriptsPath . DIRECTORY_SEPARATOR . $jsxFilename;
        file_put_contents($jsxFile, $jsxContent);

        $batScript = base_path('scripts/run_illustrator_ocrpdf.bat');
        $cmd = "cmd /C \"\"{$batScript}\" \"{$jsxFile}\" " . count($fileNames) . "\"";
        shell_exec($cmd);

        session([
            'converted_file_names' => $fileNames,
            'conversion_duration' => round(microtime(true) - $startTime, 2),
            'ocr_texts' => $ocrTexts,
            'pdf_count' => count($fileNames),
            'page_counts' => $pageCounts,
        ]);

        return response()->json(['status' => 'done']);
    }

    public function result()
    {
        return view('ocrpdf.result', [
            'fileNames' => session('converted_file_names', []),
            'duration' => session('conversion_duration', 0),
            'ocrTexts' => session('ocr_texts', []),
            'pdfCount' => session('pdf_count', 0),
            'pageCounts' => session('page_counts', []),
        ]);
    }

    public function downloadZip()
    {
        $convertedPath = storage_path('app/converted');
        $fileNames = session('converted_file_names', []);

        $zipFileName = 'converted_illustrator_files_' . now()->format('Ymd_His') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($fileNames as $filename) {
                $path = $convertedPath . '/' . $filename . '.ai';
                if (file_exists($path)) {
                    $zip->addFile($path, $filename . '.ai');
                }
            }
            $zip->close();
        }

        return Response::download($zipPath)->deleteFileAfterSend(true);
    }
}
