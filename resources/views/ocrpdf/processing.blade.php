@extends('layouts.app-layer-dua')

@section('content')
@php
    $secondsPerPage = 15;
    $bufferSeconds = 30;
    $totalPages = array_sum($pageCounts ?? []);
    $totalFiles = count($pageCounts);
    $estimatedSeconds = ($totalPages * $secondsPerPage) + $bufferSeconds;
    $estimatedMinutes = ceil($estimatedSeconds / 60);
@endphp

<div class="container text-center mt-5">
    <h3 class="mb-3">Convert PDF to Illustrator</h3>
    
    <!-- Estimasi waktu -->
    <div class="alert alert-info mx-auto" style="max-width: 600px;">
        <strong>Estimation {{ $estimatedMinutes }} minutes</strong>
        for  <strong>{{ $totalPages }} pages</strong>
        from  <strong>{{ $totalFiles }} PDF files</strong>.
    </div>

    <!-- Tampilkan nama file PDF bergantian -->
    <div class="mt-4">
        <h5 id="currentFileName" class="text-primary fw-bold"></h5>
    </div>


    <!-- Progress bar -->
    <div class="progress mx-auto my-4" style="height: 30px; max-width: 600px;">
        <div id="progressBar" class="progress-bar progress-bar-striped bg-success"
             role="progressbar" style="width: 0%; transition: width 1s;">
            0%
        </div>
    </div>

    {{-- 📄 JSON Debug Placard Zones --}}
    <div class="mt-4">
        <h5 class="mb-2 text-muted">PDF Split Path (JSON Preview)</h5>
        <div class="bg-light border rounded p-3" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem;">
            <pre>{{ json_encode($allSplitPdfPaths, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>

    

    <!-- Tombol manual -->
    <!-- <div class="mt-4">
        <p class="text-muted">Jika halaman tidak beralih otomatis:</p>
        <a href="{{ route('ocrpdf.result') }}" class="btn btn-outline-primary">
            Lihat Hasil Manual
        </a>
    </div> -->
</div>

<script>
    const estimatedSeconds = {{ $estimatedSeconds }};
    let currentSecond = 0;
    const progressBar = document.getElementById('progressBar');

    const interval = setInterval(() => {
        currentSecond++;

        let progress = Math.floor((currentSecond / estimatedSeconds) * 100);
        if (progress > 100) progress = 100;

        progressBar.style.width = progress + '%';
        progressBar.innerText = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);
            window.location.href = "{{ route('ocrpdf.result') }}";
        }
    }, 1000);
</script>

<script>
    // Kirim permintaan AJAX ke server untuk mulai proses berat
    fetch("{{ route('ocrpdf.processHeavy') }}", {
        method: "POST",
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(data => {
        console.log('Proses selesai:', data);
        window.location.href = "{{ route('ocrpdf.result') }}";
    });
</script>

<script>
    const fileNames = @json(array_keys($pageCounts));
    const fileLabel = document.getElementById('currentFileName');

    let currentIndex = 0;

    setInterval(() => {
        if (fileNames.length === 0) return;
        fileLabel.textContent = fileNames[currentIndex];
        currentIndex = (currentIndex + 1) % fileNames.length;
    }, 500);
</script>

@endsection
