<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TesnitPDFController extends Controller
{
    public function showForm()
    {
        return view('upload-form');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240', // validasi file PDF
        ]);

        $file = $request->file('file');
        $path = $file->store('pdfs'); // menyimpan file PDF ke storage

        // Proses ekstraksi dengan NitroPDF
        $content = NitroPDF::extractText(storage_path('app/' . $path));

        return view('pdf-result', ['content' => $content]);
    }
}
