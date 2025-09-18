<div class="container mt-5">
    <div class="alert alert-success">
        <h4 class="mb-3">Konversi Selesai</h4>

        @if (!empty($filenames))
            <p>Total file yang dikonversi: <strong>{{ count($filenames) }}</strong></p>
            <p>Total waktu yang ditempuh: <strong>{{ $duration ?? '-' }}</strong></p>

            <ul class="list-group mt-3">
                @foreach ($filenames as $file)
                    <li class="list-group-item">{{ $file }}</li>
                @endforeach
            </ul>
        @else
            <p>Tidak ada data file yang ditemukan.</p>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('pdf.multiple.convert') }}" class="btn btn-primary">Kembali ke Upload</a>
    </div>
</div>

