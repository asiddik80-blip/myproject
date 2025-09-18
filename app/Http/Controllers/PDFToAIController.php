<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PDFToAIController extends Controller
{
    public function index()
    {
        return view('pdfconvert.index');
    }

    public function convert(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',
        ]);

        // Path dasar
        $uploadPath = storage_path('app/uploads');
        $convertedPath = storage_path('app/converted');
        $scriptsPath = storage_path('scripts');

        // Cek permission folder
        foreach ([$uploadPath, $convertedPath, $scriptsPath] as $folder) {
            if (!is_writable($folder)) {
                return back()->withErrors(['error' => "Folder tidak writable: $folder"]);
            }
        }

        // Nama file unik
        $baseName = Str::slug(pathinfo($request->file('pdf_file')->getClientOriginalName(), PATHINFO_FILENAME));
        $uniqueId = now()->format('YmdHis') . '-' . Str::random(5);
        $filename = "{$baseName}-{$uniqueId}";

        // Simpan PDF
        $pdfPath = "{$uploadPath}/{$filename}.pdf";
        $outputPath = "{$convertedPath}/{$filename}.ai";
        $request->file('pdf_file')->move($uploadPath, "{$filename}.pdf");

        // Buat isi JSX (dengan slash aman untuk ExtendScript)
        $template = file_get_contents(resource_path('scripts/convert_pdf_to_ai_template.jsx'));
        $jsxContent = str_replace(
            ['{{INPUT_PATH}}', '{{OUTPUT_PATH}}'],
            [str_replace('\\', '/', $pdfPath), str_replace('\\', '/', $outputPath)],
            $template
        );

        // Simpan file JSX
$jsxFile = "{$scriptsPath}/generated_{$uniqueId}.jsx";
file_put_contents($jsxFile, $jsxContent);

// Cek apakah file JSX berhasil dibuat
if (!file_exists($jsxFile)) {
    return back()->withErrors(['error' => "Gagal membuat file script: $jsxFile"]);
}

// Jalankan Illustrator via batch
$batFile = base_path('scripts/run_illustrator.bat');
$jsxFileWin = str_replace('/', '\\', $jsxFile); // pastikan path Windows-style
$cmd = "cmd /C \"\"{$batFile}\" \"{$jsxFileWin}\"\"";
pclose(popen($cmd, "r")); // Non-blocking

return redirect()
    ->route('pdf.index')
    ->with('success', 'Illustrator sedang memproses file.')
    ->with('filename', "{$filename}.ai");

    }
}
