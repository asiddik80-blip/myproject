<?php

namespace App\Http\Controllers;

use App\Services\PDFService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdobePDFController extends Controller
{
    protected $pdfService;

    public function __construct(PDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    // Pastikan metode index tetap ada
    public function index()
    {
        return view('adobepdf.index');
    }

    public function extract(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:5120',
        ]);

        $file = $request->file('pdf_file');
        $filePath = $file->storeAs('public/uploads', 'PAGE_PARTLIST.pdf');
        $fullPath = storage_path("app/$filePath");

        if (!file_exists($fullPath)) {
            return back()->with('error', 'Gagal menyimpan file.');
        }

        $accessToken = $this->pdfService->getAccessToken();
        if (isset($accessToken['error'])) {
            return back()->with('error', 'Gagal mendapatkan token: ' . $accessToken['error']);
        }

        $client = new Client();
        $apiUrl = 'https://pdf-services.adobe.io/operation/extract-pdf';

        try {
            $response = $client->post($apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'x-api-key' => env('ADOBE_CLIENT_ID'),
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($fullPath, 'r'),
                        'filename' => 'PAGE_PARTLIST.pdf',
                        'headers' => [
                            'Content-Type' => 'application/pdf',
                        ],
                    ],
                ],
            ]);
        
            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Gagal mengekstrak PDF. Kode status: ' . $response->getStatusCode());
            }
        
            $output = json_decode($response->getBody(), true);
            return view('adobepdf.index', compact('output'));
        
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorResponse = $e->getResponse();
            $errorMessage = 'Gagal mengekstrak PDF.';
        
            if ($errorResponse) {
                $errorBody = (string) $errorResponse->getBody();
                Log::error('Adobe API Error:', ['body' => $errorBody]); // Simpan ke log
                return back()->with('error', 'Gagal mengekstrak PDF. Response API: ' . $errorBody);
            } else {
                return back()->with('error', 'Gagal mengekstrak PDF: ' . $e->getMessage());
            }
        
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengekstrak PDF: ' . $e->getMessage());
        }
        
    }
}
