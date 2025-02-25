<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Models\ExtractedTable;

class ExtractPDFController extends Controller
{
    public function index()
    {
        return view('extractpdf.index', ['output' => []]);
    }

    public function dimanaPython()
    {
        // 🔹 Cek apakah berjalan di Windows atau Linux
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $pythonCheck = shell_exec("where python");
        } else {
            $pythonCheck = shell_exec("which python3") ?: shell_exec("which python");
        }

        // 🔹 Ambil path pertama jika ada beberapa hasil
        $pythonPaths = array_filter(array_map('trim', explode("\n", trim($pythonCheck))));
        $pythonPath = $pythonPaths[0] ?? 'C:\Program Files\Python313\python.exe'; // Fallback jika tidak ditemukan

        return $pythonPath; // ❌ Jangan pakai tanda kutip tambahan di sini
    }




    public function jalankanPythonScript($pdfFilePath)
    {
        $pythonPath = $this->dimanaPython();
        $scriptPath = storage_path('app/python-scripts/extract_table.py');

        // 🔹 Gunakan tanda kutip untuk path Python jika ada spasi
        $command = "\"$pythonPath\" " . escapeshellarg($scriptPath) . " " . escapeshellarg($pdfFilePath) . " 2>&1";
        $output = shell_exec($command);

        if ($output === null || trim($output) === '') {
            return [
                'error' => 'Gagal menjalankan script atau tidak ada output.',
                'command' => $command,
            ];
        }

        // 🔹 Bersihkan karakter aneh yang bisa mengganggu JSON
        $cleanOutput = trim($output);
        $cleanOutput = preg_replace('/[\x00-\x1F\x7F]/', '', $cleanOutput);

        // 🔹 Cek apakah JSON valid
        $decodedOutput = json_decode($cleanOutput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Gagal mendecode JSON.',
                'json_error' => json_last_error_msg(),
                'output' => $cleanOutput,  // Tampilkan output asli untuk debugging
                'command' => $command,
            ];
        }

        return $decodedOutput;
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

        $output = $this->jalankanPythonScript($pdfFilePath);

        return response()->json(['output' => $output]);
    }
}
