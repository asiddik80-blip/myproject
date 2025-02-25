@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF - Extract Table dengan API Adobe (SHENZEN)') }}
            </h2>
            <p class="mb-3">Silakan pilih berkas PDF yang akan diupload (blade shenzen)</p>

            <!-- Tampilkan pesan error jika ada -->
            @if(session('error'))
                <div class="alert alert-danger">
                    <strong>Error:</strong> {{ session('error') }}
                    @if(session('details'))
                        <div class="mt-2">
                            <strong>Details:</strong>
                            <pre style="white-space: pre-wrap; word-wrap: break-word; background: #f4f4f4; padding: 10px; border-radius: 5px;">
                                Output: {{ session('details')['output'] }}
                                Error: {{ session('details')['error'] }}
                            </pre>
                        </div>
                    @endif
                </div>
            @endif

            <form action="{{ url('/extract') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="pdf_file" accept="application/pdf" required>
                    <button class="btn btn-danger" type="submit">Upload & Extract</button>
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