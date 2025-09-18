@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">PDF convert to Illustrator</h3>
    <label for="pdf_files" class="form-label">Auto detect Windows or Macintosh, choose 10 PDF files</label>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Error:</strong> {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('pdf.macwin.convert') }}" method="POST" enctype="multipart/form-data">
            
                @csrf
                <input type="file" name="pdf_files[]" multiple required accept="application/pdf">
                <button class="btn btn-warning mt-2" type="submit">Convert PDFs to AI</button>
    </form>
</div>
@endsection
