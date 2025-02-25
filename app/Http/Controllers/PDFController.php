<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PDFData;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PDFController extends Controller
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
        // Simpan file di storage (dalam folder pdfs)
        $pdfPath = $request->file('pdf')->store('pdfs');
        $absolutePath = storage_path("app/$pdfPath");

        // Pastikan file ada sebelum menjalankan Python
        if (!file_exists($absolutePath)) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        // Pastikan menggunakan path absolut ke Python jika perlu
        $pythonPath = "python"; // Ubah ke path absolut jika error di Windows
        $scriptPath = base_path("python-scripts/extract_table.py");

        // Gunakan escapeshellarg untuk keamanan
        $process = new Process([
            $pythonPath,
            $scriptPath,
            escapeshellarg($absolutePath),
            "1"
        ]);

        // Jalankan script
        $process->run();

        // Jika ada error, tangani dengan baik
        if (!$process->isSuccessful()) {
            return response()->json([
                'error' => 'Python script execution failed.',
                'details' => $process->getErrorOutput(),
            ], 500);
        }

        // Berhasil, kirimkan hasilnya sebagai JSON
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
