<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AdobeEkstrakPDFController extends Controller
{
    public function index()
    {
        return view('adobeekstrakpdf.index');
    }

    public function extract(Request $request)
    {
        // Validasi file input harus PDF
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048'
        ]);

        // Simpan file yang diupload
        $file = $request->file('pdf_file');
        $fileName = time() . '-' . $file->getClientOriginalName();
        $filePath = storage_path('app/public/' . $fileName);
        $file->move(storage_path('app/public/'), $fileName);

        // Jalankan script Python untuk ekstraksi
        $pythonScript = base_path('extract.py'); // Sesuaikan path script Python
        $process = new Process(['python', $pythonScript, $filePath]);
        $process->run();

        // Tangani error jika proses gagal
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ambil output dari script Python
        $output = json_decode($process->getOutput(), true);

        return view('adobeekstrakpdf.index', compact('output'));
    }
}
