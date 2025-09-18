@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Upload Berkas PDF - Extract Table') }}
            </h2>
            <p class="mb-3">Silakan pilih berkas PDF yang akan diupload</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ url('syauqiepdf/extract') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="file" name="pdf_file" class="form-control" accept="application/pdf" required>
                    <button class="btn btn-danger" type="submit">Extract</button>
                </div>
            </form>

        </div>
    </div>
@endsection
