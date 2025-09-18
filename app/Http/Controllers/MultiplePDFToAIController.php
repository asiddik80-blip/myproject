<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MultiplePDFToAIController extends Controller
{
    public function index()
    {
        return view('multiplepdfconvert.index');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_files' => 'required|array|max:10',
            'pdf_files.*' => 'mimes:pdf|max:10240',
        ]);

        $uploadPath = storage_path('app/uploads');
        $convertedPath = storage_path('app/converted');
        $scriptsPath = storage_path('scripts');

        foreach ([$uploadPath, $convertedPath, $scriptsPath] as $folder) {
            if (!is_writable($folder)) {
                return back()->withErrors(['error' => "Folder tidak writable: $folder"]);
            }
        }

        $fileList = [];

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
        }

        // Buat file JSX dari template
        $template = file_get_contents(resource_path('scripts/convert_multiple_pdfs_template.jsx'));
        $jsArrayContent = collect($fileList)->map(function ($file) {
            return "{input: \"{$file['input']}\", output: \"{$file['output']}\"}";
        })->implode(",\n    ");

        $jsxContent = str_replace('{{FILES_ARRAY}}', $jsArrayContent, $template);

        $jsxFile = $scriptsPath . '/generated_batch_' . now()->format('YmdHis') . '.jsx';
        file_put_contents($jsxFile, $jsxContent);

        if (!file_exists($jsxFile)) {
            return back()->withErrors(['error' => "Gagal membuat file script: $jsxFile"]);
        }

        $batFile = base_path('scripts/run_illustrator_batch.bat');
        $cmd = "cmd /C \"\"{$batFile}\" \"{$jsxFile}\"\"";
        pclose(popen($cmd, "r"));

        // Simpan nama file & waktu mulai di session
        session([
            'filenames' => collect($fileList)->pluck('input')->map(function ($path) {
                return basename($path);
            })->toArray(),
            'start_time' => now(),
        ]);

        return redirect()->route('pdf.multiple.processing');
    }

    public function processing()
    {
        return view('multiplepdfconvert.processing');
    }

    public function result()
    {
        $filenames = session('filenames', []);
        $start = session('start_time');

        $duration = null;
        if ($start) {
            $end = now();
            $seconds = $end->diffInSeconds($start);
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            $duration = ($minutes ? $minutes . ' menit ' : '') . $remainingSeconds . ' detik';
        }

        return view('multiplepdfconvert.result', compact('filenames', 'duration'));
    }
}
