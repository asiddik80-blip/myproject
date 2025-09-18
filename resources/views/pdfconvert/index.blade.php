@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Extract PDF to Ai') }}
            </h2>

            <p class="mb-3">Choose your PDF file</p>

            {{-- Tampilkan error jika ada --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Tampilkan pesan sukses dan link download --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                    @if(session('filename'))
                        <br>
                        <strong>Hasil:</strong> {{ session('filename') }}
                        <br>
                        <a href="{{ asset('storage/app/converted/' . session('filename')) }}" class="btn btn-sm btn-success mt-2" download>
                            Download {{ session('filename') }}
                        </a>
                    @endif
                </div>
            @endif

            {{-- Form upload --}}
            <form action="{{ route('pdf.convert') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="pdf_file" required>
                <button class="btn btn-primary mt-2" type="submit">Convert to .AI (Illustrator file)</button>
            </form>
        </div>
    </div>
@endsection
