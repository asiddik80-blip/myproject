<?php

use App\Services\PDFService;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ExtractPDFController;
use App\Http\Controllers\TabulaPartListController;
use App\Http\Controllers\OCRPartListController;
use App\Http\Controllers\NitroPDFController;
use App\Http\Controllers\TesnitPDFController;

use App\Http\Controllers\AdobePDFController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdobeEkstrakPDFController;
use App\Http\Controllers\PDFconodejsController;
use App\Http\Controllers\SyauqiePDFController;
use App\Http\Controllers\ShenzenPDFController;

use App\Http\Controllers\FormdataController;

use App\Http\Controllers\PDFToAIController;
use App\Http\Controllers\MultiplePDFToAIController;
use App\Http\Controllers\MacwinController;
use App\Http\Controllers\CekVektorPDFController;
use App\Http\Controllers\OcrpdfController;

use App\Http\Controllers\DebugPageDController;

Route::get('/set-language/{lang}', function ($lang) {
    session(['locale' => $lang]);
    return redirect()->back();
})->name('setLanguage');

// Pastikan middleware SetLocale diaktifkan untuk semua route dalam grup ini
Route::middleware([\App\Http\Middleware\SetLocale::class])->group(function () {
    
    
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/register', function () {
        return redirect()->route('login');
    });

    Route::post('/register', function () {
        return redirect()->route('login');
    });


    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
        
        Route::get('/sejarah', function () {
            return view('sejarah');
        })->name('sejarah');

        Route::get('/uploadpdf', [PDFController::class, 'show'])->name('showpdf');

        
        //form
        Route::get('/formdata', [FormdataController::class, 'index'])->name('formdata.index');

        Route::post('/uploadpdf', [PDFController::class, 'uploadpdf'])->name('uploadpdf');
        //Route::get('/extract-table', [PDFController::class, 'extractTable']);
        Route::post('/extract-table', [PDFController::class, 'extractTable']);

        //percobaan ekstrak pdf yang kedua
        Route::get('/ekstrakpdf', [ExtractPDFController::class, 'index']);
        Route::post('/ekstrakpdf', [ExtractPDFController::class, 'process']);

        //percobaan ekstrak pdf untuk tabel Part List dengan Tabula
        Route::get('/tabulapartlist', [TabulaPartListController::class, 'index']);
        Route::post('/tabulapartlist', [TabulaPartListController::class, 'process']);

        //percobaan ekstrak pdf untuk tabel Part List dengan OCR
        Route::get('/ocrpartlist', [OCRPartListController::class, 'index']);
        Route::post('/ocrpartlist', [OCRPartListController::class, 'process']);

        //Percobaan ekstrak PDF dengan Nitro
        Route::get('/nitro', [NitroPDFController::class, 'showForm']);
        Route::post('/nitro', [NitroPDFController::class, 'upload'])->name('nitro.upload');

        //Percobaan ekstrak PDF dengan API Adobe
        Route::get('/adobepdf', [AdobePDFController::class, 'index']);
        Route::post('/adobepdf/extract', [AdobePDFController::class, 'extract']);

        
        
        // tes nitro awal chatgpt
        Route::get('/upload-pdf', [tesnitPDFController::class, 'showForm']);
        Route::post('/upload-pdf', [tesnitPDFController::class, 'upload'])->name('pdf.upload');

        //PDF CO dan Node JS untuk PDF ke JSON
        Route::get('/pdfco', function () {
            return view('pdfco.index');
        });

        Route::post('/pdfco/upload', [PDFconodejsController::class, 'upload'])->name('pdfco.upload');


        //Syauqie PDF
        Route::get('/syauqiepdf', [SyauqiePDFController::class, 'index'])->name('syauqiepdf.index');
        Route::post('/syauqiepdf/extract', [SyauqiePDFController::class, 'extract'])->name('syauqiepdf.extract');
        Route::get('/extract', [SyauqiePDFController::class, 'extract']);

        //Shenzen PDF
        Route::get('/shenzen', [ShenzenPDFController::class, 'index'])->name('shenzen.index');
        Route::post('/shenzen/extract', [ShenzenPDFController::class, 'extract'])->name('shenzen.extract');
        
        
        //coba PDF to Adobe Illustrator file
        Route::get('/convert-pdf', [PDFToAIController::class, 'index'])->name('pdf.index');
        Route::post('/convert-pdf', [PDFToAIController::class, 'convert'])->name('pdf.convert');

        // coba PDF to Adobe Illustrator file dengan menu multiple file
        Route::get('/convert-multiple-pdf', [MultiplePDFToAIController::class, 'index'])->name('multiplepdfconvert.index');
        Route::post('/convert-multiple-pdf', [MultiplePDFToAIController::class, 'convert'])->name('pdf.multiple.convert');
        Route::get('/convert-multiple-pdf/result', [MultiplePDFToAIController::class, 'result'])->name('pdf.multiple.result');
        Route::get('/convert-multiple-pdf/processing', [MultiplePDFToAIController::class, 'processing'])->name('pdf.multiple.processing');
        
        //coba PDF to Adobe Illustrator file dengan menu multiple file, dengan OS dipilih secara dinamis, Windows atau Mac
        Route::prefix('macwinpdf')->group(function () {
            Route::get('/', [MacwinController::class, 'index'])->name('pdf.macwin.index');
            Route::post('/convert', [MacwinController::class, 'convert'])->name('pdf.macwin.convert');
            Route::get('/processing', [MacwinController::class, 'processing'])->name('pdf.macwin.processing');
            Route::get('/result', [MacwinController::class, 'result'])->name('pdf.macwin.result');
        });


        //Test upload PDF dan convert ke .ai dengan pengecekan vektor 
            Route::prefix('cekvektorpdf')->name('pdf.cekvektor.')->group(function () {
                Route::get('/', [CekVektorPDFController::class, 'index'])->name('index');
                Route::post('/convert', [CekVektorPDFController::class, 'convert'])->name('convert');
                Route::get('/processing', [CekVektorPDFController::class, 'processing'])->name('processing');
                Route::get('/result', [CekVektorPDFController::class, 'result'])->name('result');
        });


        //Convert PDF ke OCR lalu ke Illustrator
        Route::prefix('ocrpdf')->name('ocrpdf.')->group(function () {
        Route::get('/', [OcrpdfController::class, 'index'])->name('index');
        Route::post('/convert', [OcrpdfController::class, 'convert'])->name('convert');
        Route::get('/processing', [OcrpdfController::class, 'processing'])->name('processing');
        Route::get('/result', [OcrpdfController::class, 'result'])->name('result');
        Route::post('/process-heavy', [OcrpdfController::class, 'processHeavy'])->name('processHeavy');
    });

        Route::get('/ocrpdf/download-zip', [OcrpdfController::class, 'downloadZip'])->name('ocrpdf.downloadZip');



        //Route untuk Uji Coba Helper (Debugging)
        Route::prefix('debugpaged')->name('debugpaged.')->group(function () {
            Route::get('/', [DebugPageDController::class, 'index'])->name('index');
            Route::post('/process', [DebugPageDController::class, 'process'])->name('process');
        });
        
        // Testing JVM via Laravel
        Route::get('/test-jvm', [TestController::class, 'runJVM']);

        Route::get('/run-java', [TestController::class, 'runJava']);
        Route::get('/whoami', [TestController::class, 'runWhoami']);
        Route::get('/scanclassjava', [TestController::class, 'cekClassJava']);
        Route::get('/cekPython', [TestController::class, 'cekPythonPath']);
        Route::get('/cek-python-modules', [TestController::class, 'cekPythonModules']);
        Route::get('/cek-python-where', [TestController::class, 'dimanaPython']);


        //Mengambil Access Token Adobe
        Route::get('/get-token', function () {
            $pdfService = new PDFService();
            return response()->json(['token' => $pdfService->getAccessToken()]);
        });
        
        // Testing Adobe lagi

        Route::get('/adobeekstrakpdf', [AdobeEkstrakPDFController::class, 'index'])->name('adobeekstrakpdf.index');
        Route::post('/adobeekstrakpdf/extract', [AdobeEkstrakPDFController::class, 'extract'])->name('adobeekstrakpdf.extract');


    });
});
