<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TabulaPartListController extends Controller
{
    public function index()
    {
        return view('tabulapartlist.index', ['output' => []]);
    }

    public function dimanaPython()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pythonCheck = shell_exec("where python");
        } else {
            $pythonCheck = shell_exec("which python3") ?: shell_exec("which python");
        }

        $pythonPaths = array_filter(array_map('trim', explode("\n", trim($pythonCheck))));
        return $pythonPaths[0] ?? 'C:\\Program Files\\Python313\\python.exe';
    }


    public function extractWithNitro(Request $request)
    {
        $file = $request->file('pdf_file');
        if (!$file) {
            return response()->json(['error' => 'File PDF tidak ditemukan'], 400);
        }

        // Simpan file di storage
        $fileName = time() . '_' . $file->getClientOriginalName();
        $pdfFilePath = storage_path('app/pdfs/' . $fileName);
        $file->move(storage_path('app/pdfs/'), $fileName);

        // Lokasi NitroPDF (sesuaikan dengan instalasi)
        $nitroPath = '"C:\Program Files\Nitro\Nitro Pro\NitroPDF.exe"';
        $outputTxt = storage_path('app/pdfs/' . pathinfo($fileName, PATHINFO_FILENAME) . '.txt');

        // Jalankan perintah NitroPDF
        $command = "$nitroPath --extract-text \"$pdfFilePath\" \"$outputTxt\"";
        shell_exec($command);

        // Baca hasil ekstraksi
        if (file_exists($outputTxt)) {
            $text = file_get_contents($outputTxt);
            return response()->json([
                'file_name' => $fileName,
                'extracted_text' => $text ?: 'Teks tidak ditemukan'
            ]);
        } else {
            return response()->json(['error' => 'Gagal mengekstrak teks dengan NitroPDF']);
        }
    }

    public function jalankanPythonScript($pdfFilePath, $fileName)
    {
        $pythonPath = $this->dimanaPython();
        $scriptPath = storage_path('app/python-scripts/extract_text.py');

        // NitroPDF sudah mengekstrak teks ke file .txt, jadi kita panggil script Python untuk membacanya
        $command = "\"$pythonPath\" " . escapeshellarg($scriptPath) . " " . escapeshellarg($pdfFilePath) . " 2>&1";
        $output = shell_exec($command);

        if ($output === null || trim($output) === '') {
            return [
                'error' => 'Gagal menjalankan script atau tidak ada output.',
                'command' => $command,
                'file_name' => $fileName
            ];
        }

        return json_decode($output, true);
    }


    public function process(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:2048',
        ]);

        $file = $request->file('pdf_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $pdfFilePath = storage_path('app/pdfs/' . $fileName);
        $file->move(storage_path('app/pdfs/'), $fileName);

        return $this->jalankanPythonScript($pdfFilePath, $fileName);
    }

    public function extractPdfText(Request $request)
    {
        $file = $request->file('pdf_file');

        if (!$file) {
            return response()->json(['error' => 'File PDF tidak ditemukan'], 400);
        }

        // Simpan file sementara di penyimpanan Laravel
        $filePath = $file->storeAs('temp', $file->getClientOriginalName());

        // Jalankan skrip Python dengan path file sebagai argumen
        $process = new Process(["python3", base_path('scripts/extract_text.py'), storage_path("app/$filePath")]);
        $process->run();

        if (!$process->isSuccessful()) {
            return response()->json([
                'error' => 'Gagal menjalankan skrip Python.',
                'details' => $process->getErrorOutput()
            ], 500);
        }

        // Ambil output dari Python (harus berupa string hasil ekstraksi)
        $output = json_decode($process->getOutput(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'Output dari Python bukan JSON valid.',
                'raw_output' => $process->getOutput()
            ], 500);
        }

        return response()->json($output);
    }
}
