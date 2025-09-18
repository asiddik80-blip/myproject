<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MacwinController extends Controller
{
    public function index()
    {
        return view('macwinpdf.index');
    }

    public function processing()
    {
        return view('macwinpdf.processing');
    }

    public function result(Request $request)
    {
        $fileNames = session('converted_file_names', []);
        $duration = session('conversion_duration', 0);

        return view('macwinpdf.result', compact('fileNames', 'duration'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_files' => 'required|array|max:10',
            'pdf_files.*' => 'mimes:pdf|max:10240',
        ]);

        $startTime = microtime(true);

        $uploadPath = storage_path('app/uploads');
        $convertedPath = storage_path('app/converted');
        $scriptsPath = storage_path('scripts');

        foreach ([$uploadPath, $convertedPath, $scriptsPath] as $folder) {
            if (!is_writable($folder)) {
                return back()->withErrors(['error' => "Folder tidak writable: $folder"]);
            }
        }

        $fileList = [];
        $fileNames = [];

        foreach ($request->file('pdf_files') as $file) {
            $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $uniqueId = now()->format('YmdHis') . '-' . Str::random(5);
            $filename = "{$baseName}-{$uniqueId}";

            $pdfPath = "$uploadPath/{$filename}.pdf";
            $outputPath = "$convertedPath/{$filename}.ai";
            $file->move($uploadPath, "{$filename}.pdf");

            $fileList[] = [
                'input' => str_replace('\\', '/', $pdfPath),
                'output' => str_replace('\\', '/', $outputPath),
            ];

            $fileNames[] = "{$filename}.pdf";
        }

        // Buat file JSX
        $template = file_get_contents(resource_path('scripts/convert_multiple_pdfs_template.jsx'));
        $jsArrayContent = collect($fileList)->map(fn($file) =>
            "{input: \"{$file['input']}\", output: \"{$file['output']}\"}"
        )->implode(",\n    ");

        $jsxContent = str_replace('{{FILES_ARRAY}}', $jsArrayContent, $template);
        $jsxFilename = 'generated_batch_' . now()->format('YmdHis') . '.jsx';
        $jsxFile = $scriptsPath . DIRECTORY_SEPARATOR . $jsxFilename;
        file_put_contents($jsxFile, $jsxContent);

        if (!file_exists($jsxFile)) {
            return back()->withErrors(['error' => "Gagal membuat file script: $jsxFile"]);
        }

        // Deteksi OS dan siapkan path JSX sesuai OS
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $jsxFileFormatted = $isWindows ? str_replace('/', '\\', $jsxFile) : $jsxFile;

        // Jalankan skrip sesuai OS
        $batScript = base_path('scripts/run_illustrator_macwin.bat');
        $shScript = base_path('scripts/run_illustrator_macwin.sh');

        $cmd = $isWindows
            ? "cmd /C \"\"{$batScript}\" \"{$jsxFileFormatted}\"\""
            : "sh \"$shScript\" \"$jsxFileFormatted\"";

        pclose(popen($cmd, "r"));

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        return redirect()
            ->route('pdf.macwin.processing')
            ->with('converted_file_names', $fileNames)
            ->with('conversion_duration', $duration);
    }
}
