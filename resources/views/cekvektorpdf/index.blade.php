@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Cek PDF Vektor (Max 5 file)</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pdf.cekvektor.convert') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="pdf_files" class="form-label">Pilih file PDF (maksimal 5 file):</label>
            <input type="file" name="pdf_files[]" id="pdf_files" class="form-control" multiple accept=".pdf" required>
        </div>
        <button type="submit" class="btn btn-success">Upload & Cek Vektor</button>
    </form>
</div>
@endsection
