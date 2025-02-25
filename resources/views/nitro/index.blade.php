@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF - Extract Table Part List dengan Nitro PDF') }}
            </h2>
            <p class="mb-3">Silakan pilih berkas PDF yang akan diupload</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ url('/nitro') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="filepdf" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-primary" type="submit">Extract</button>
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

        <!-- Authentication -->
<form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <a href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('NITRO Log Out') }}
                                </a>
                            </form>
    </div>
@endsection
