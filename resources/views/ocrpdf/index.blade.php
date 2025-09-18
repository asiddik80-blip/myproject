@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Convert PDF to Illustrator file</h3>

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

    <form action="{{ route('ocrpdf.convert') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
        @csrf
        <label for="pdf_files" class="form-label">Choose PDF file</label>
        <input type="file" name="pdf_files[]" multiple required accept="application/pdf" class="form-control" id="pdf_files">

        <button class="btn btn-success mt-3" type="submit" id="submitBtn">Convert</button>
    </form>

    <hr class="my-5">

    <div>
        <main>
    <h5 class="text-danger">Feature Overview</h5>
    <ol>
      <li>1. The main feature of this website is converting PDF files to Illustrator files (<code>.ai</code> format.).</li>
      <li>2. The maximum number of files that can be converted in a single process is 1 PDF files (maximum of 10 pages).</li>
      <li>3. The most accurate PDFs for processing are “Drawing Placard” files, with image captions such as “ITEM-xxx,” for example: <code>A511351610 Rev--</code>.</li>
      <li>4. For each file processed, information about the page types will be displayed, such as Cover, Placard images, and Revisions pages. Identification for Partlist pages is not yet available and is still under development.</li>
      <li>5. If some Placard content results in strange words, it means that area still requires accuracy improvement, as it may contain Arabic text or noise. For example, text like <code>a5 UU Aa gl!</code> and similar.</li>
      <li>6. We provide several sample PDF files in the style of <code>A511351610 Rev--</code>, with varying page counts, for multi-file testing. If there are other files with similar patterns, you can also try converting them.</li>
    </ol>
    
  </main>
    </div>
</div>

@endsection
