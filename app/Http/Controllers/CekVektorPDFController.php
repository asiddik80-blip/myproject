<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CekVektorPDFController extends Controller
{
    public function index()
    {
        return view('cekvektorpdf.index');
    }

    public function processing()
    {
        return view('cekvektorpdf.processing');
    }

    public function result(Request $request)
    {
        $fileNames = session('converted_file_names', []);
        $duration = session('conversion_duration', 0);

        return view('cekvektorpdf.result', compact('fileNames', 'duration'));
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_files' => 'required|array|max:5',
            'pdf_files.*' => 'mimes:pdf|max:10240', // max 10 MB per file
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

            $pdfPath = "{$uploadPath}/{$filename}.pdf";
            $outputPath = "{$convertedPath}/{$filename}.ai";
            $file->move($uploadPath, "{$filename}.pdf");

            // Gunakan realpath dan ubah ke format Windows-style
            $inputRealPath = str_replace('/', '\\', realpath($pdfPath));
            $outputRealPath = str_replace('/', '\\', realpath(dirname($outputPath))) . "\\{$filename}.ai";

            $fileList[] = [
                'input' => $inputRealPath,
                'output' => $outputRealPath,
            ];

            $fileNames[] = "{$filename}.pdf";
        }

        // Generate JSX dari template
        $templatePath = resource_path('scripts' . DIRECTORY_SEPARATOR . 'cekvektor_template.jsx');

        if (!file_exists($templatePath)) {
            return back()->withErrors(['error' => "Template tidak ditemukan: $templatePath"]);
        }

        $template = file_get_contents($templatePath);
        $jsArrayContent = collect($fileList)->map(fn($file) =>
            "{input: \"{$file['input']}\", output: \"{$file['output']}\"}"
        )->implode(",\n    ");

        $jsxContent = str_replace('{{FILES_ARRAY}}', $jsArrayContent, $template);

        $jsxFileName = 'generated_vektor_' . now()->format('YmdHis') . '.jsx';
        $jsxFilePath = $scriptsPath . DIRECTORY_SEPARATOR . $jsxFileName;

        file_put_contents($jsxFilePath, $jsxContent);

        if (!file_exists($jsxFilePath)) {
            return back()->withErrors(['error' => "Gagal membuat file script: $jsxFilePath"]);
        }

        // Jalankan batch file
        $batScript = base_path('scripts/run_cekvektor.bat');
        $jsxRealPath = str_replace('/', '\\', realpath($jsxFilePath));

        $cmd = "cmd /C \"\"{$batScript}\" \"{$jsxRealPath}\"\"";
        pclose(popen($cmd, "r"));

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        return redirect()
            ->route('pdf.cekvektor.processing')
            ->with('converted_file_names', $fileNames)
            ->with('conversion_duration', $duration);
    }
}
