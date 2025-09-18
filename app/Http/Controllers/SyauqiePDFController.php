<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SyauqiePDFController extends Controller
{
    public function index()
    {
        return view('syauqiepdf.index');
    }

    public function extract(Request $request)
    {
        // Validasi file upload
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:20480', // Maksimal 20MB
        ]);
    
        // Simpan file PDF ke storage Laravel
        $pdf = $request->file('pdf_file');
        $pdfPath = storage_path('app/pdfs/' . $pdf->hashName());
        $pdf->storeAs('pdfs', $pdf->hashName());
    
        // Path credentials Adobe
        $credentialPath = base_path('pdfservices-api-credentials.json');
    
        // Pastikan file PDF dan credentials ada
        if (!file_exists($pdfPath) || !file_exists($credentialPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File PDF atau credentials tidak ditemukan!'
            ], 400);
        }
    
        // Menjalankan script Python dengan shell_exec()
        $command = escapeshellcmd("python " . base_path('extract.py') . " " . $pdfPath . " " . $credentialPath . " 2>&1");
        $output = shell_exec($command);
    
        // Cek apakah ada error
        if (!$output) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ekstraksi gagal!',
                'error' => 'Tidak ada output dari Python script.'
            ], 500);
        }
    
        // Decode output JSON
        $jsonOutput = json_decode($output, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => 'error',
                'message' => 'Output Python bukan JSON yang valid!',
                'error' => json_last_error_msg()
            ], 500);
        }
    
        // Kirim data ke Blade
        return view('syauqiepdf.hasil', compact('jsonOutput'));
    }
    



}