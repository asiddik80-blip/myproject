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
        $pdfPath = storage_path('app/pdfs/sample.pdf'); // Sesuaikan path
        $credentialPath = base_path('pdfservices-api-credentials.json'); // Sesuaikan path

        $process = new Process([
            "C:\\Program Files\\Python313\\python.EXE",
            base_path("extract.py"),
            $pdfPath,
            $credentialPath
        ]);

        $process->run();

        // Cek jika terjadi error
        if (!$process->isSuccessful()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ekstraksi gagal!',
                'error' => $process->getErrorOutput()
            ], 500);
        }

        // Tangkap output JSON dari Python
        $output = json_decode($process->getOutput(), true);

        return response()->json([
            'status' => 'success',
            'data' => $output
        ]);
    }



}
