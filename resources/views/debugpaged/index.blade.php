@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Uji Coba Caption Detector (Page D)</h3>
    <form action="{{ route('debugpaged.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="ocr_json" class="form-label">Upload OCR JSON</label>
            <input type="file" name="ocr_json" id="ocr_json" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="image_file" class="form-label">Upload Image (JPG/PNG)</label>
            <input type="file" name="image_file" id="image_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Proses</button>
    </form>
</div>
@endsection
