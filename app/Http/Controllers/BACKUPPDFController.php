<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PDFData;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BACKUPPDFController extends Controller
{
    public function uploadpdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048',
        ]);

        // Simpan file PDF ke storage/app/public/pdfs/
        $file = $request->file('pdf_file');
        $path = $file->store('pdfs', 'public'); // Simpan di storage/app/public/pdfs/

        // Path yang benar untuk membaca file
        $fullPath = storage_path('app/public/' . $path);

        // Debugging: Periksa apakah file ada
        if (!file_exists($fullPath)) {
            return back()->with('error', 'File tidak ditemukan: ' . $fullPath);
        }

        // Parsing PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        // Simpan ke database
        PDFData::create([
            'filename' => $file->getClientOriginalName(),
            'content' => $text,
        ]);

        return back()->with('success', 'File berhasil diunggah dan diproses!');
    }

    public function show()
    {
        
        $pdfData    = PDFData::orderBy('filename', 'asc')->get();
        if (!$pdfData) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        return view('datapdf.index', compact('pdfData'));
    }

    
    public function extractTable(Request $request)
    {
        $pdfPath = $request->file('pdf')->store('pdfs'); // Simpan file di storage
        $absolutePath = storage_path("app/$pdfPath");

        $process = new Process(["python", base_path("python-scripts/extract_table.py"), $absolutePath, "1"]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return response()->json(json_decode($process->getOutput(), true));
    }

    public function extractPdf()
    {
        $pdfPath = storage_path('app/public/sample.pdf'); // Pastikan file ada
        $pythonScript = base_path('python-scripts/extract_pdf.py'); // Simpan di folder project
        $page = 1; // Tentukan halaman yang akan di-extract

        // Pastikan "python" dikenali di sistem (bisa pakai path absolut ke python.exe jika perlu)
        $command = "python \"$pythonScript\" \"$pdfPath\" \"$page\"";

        $output = shell_exec($command); // Jalankan script Python

        return response()->json(['message' => $output]);
    }


}
