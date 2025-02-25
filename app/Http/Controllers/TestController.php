<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function runJVM()
    {
        $output = shell_exec('python -c "import jpype; jpype.startJVM(); print(\"JVM started!\")"');
        return response()->json(['message' => trim($output)]);
    }

    public function runJava()
    {
        $directory = base_path('resources/views/java'); // Pastikan ini benar
        $command = "cd \"$directory\" && java -cp . HelloJava 2>&1"; // Jalankan Java
        
        $output = shell_exec($command); // Eksekusi command

        return response()->json([
            'command' => $command, // Lihat perintah yang dijalankan
            'message' => trim($output ?: 'Java tidak mengeluarkan output!')
        ]);
    }

    public function runWhoami()
    {
        $user = shell_exec('whoami');
        return response()->json(['user' => trim($user)]);
    }

    public function cekClassJava()
    {
        $directory = base_path('resources/views/java'); // Pastikan path benar
        $files = scandir($directory); // Cek isi folder
    
        return response()->json([
            'directory' => $directory,
            'files' => $files
        ]);
    }

    public function cekPythonPath()
    {
        $pythonPath = shell_exec('python -c "import sys; print(sys.executable)"');
        return response()->json(['python_path' => trim($pythonPath)]);
    }

    public function cekPythonModules()
    {
        $pythonPath = '"C:\\Program Files\\Python313\\python.exe"';
        $command = "$pythonPath -m pip list";
        $output = shell_exec($command);

        return response()->json(['modules' => nl2br($output)]);
    }

    public function dimanaPython()
    {
        $pythonCheck = shell_exec("where python");
        $pythonPaths = explode("\n", trim($pythonCheck)); // Pisahkan berdasarkan newline
        $pythonPath = $pythonPaths[0] ?? null; // Ambil path pertama

        return response()->json(['python_path' => trim($pythonPath)]);
    }




}
