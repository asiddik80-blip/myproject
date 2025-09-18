@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Convert Multiple PDFs to AI') }}
            </h2>
            <p class="mb-3">Select up to 10 PDF files</p>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                    <ul>
                        @foreach(session('filenames', []) as $file)
                            <li>{{ $file }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pdf.multiple.convert') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="pdf_files[]" multiple required accept="application/pdf">
                <button class="btn btn-primary mt-2" type="submit">Convert PDFs to AI</button>
            </form>
        </div>
    </div>
@endsection
