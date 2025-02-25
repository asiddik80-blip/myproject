<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PDFconodejsController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048',
        ]);

        // Simpan file ke storage Laravel
        $pdfFile = $request->file('pdf_file');
        $filePath = storage_path('app/' . $pdfFile->store('pdfs'));

        // Jalankan skrip Node.js
        $process = new Process(['node', base_path('skrip/extractpdfco.js'), $filePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ambil hasil JSON
        $jsonResult = json_decode(file_get_contents(storage_path('app/result.json')), true);

        return response()->json($jsonResult);
    }
}
