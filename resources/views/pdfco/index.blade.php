@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF - Extract Table dengan PDF co Node JS') }}
            </h2>
            <p class="mb-3">Silakan pilih berkas PDF yang akan diupload (pdfco)</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('pdfco.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="pdf_file" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-success" type="submit">Extract PDFCO</button>
                </div>
            </form>

            <!-- Tampilkan hasil ekstrak sebagai JSON -->
            @if(!empty($output))
                <div class="mt-4">
                    <h3>Hasil Ekstraksi (JSON):</h3>
                    <pre style="white-space: pre-wrap; word-wrap: break-word; background: #f4f4f4; padding: 10px; border-radius: 5px;">
                        {{ json_encode($output, JSON_PRETTY_PRINT) }}
                    </pre>
                </div>
            @else
                <p class="text-muted mt-3">Belum ada hasil ekstraksi.</p>
            @endif
        </div>
    </div>
@endsection
