<div class="container mt-5">
    <h3 class="mb-4">Konversi Selesai</h3>

    <div class="alert alert-success">
        <strong>Berhasil!</strong> File telah dikonversi menggunakan Adobe Illustrator.
    </div>

    <p><strong>Total Waktu Proses:</strong> {{ $duration }} detik</p>

    <h5>Daftar File yang Dikonversi:</h5>
    <ul class="list-group">
        @foreach ($fileNames as $name)
            <li class="list-group-item">{{ $name }}</li>
        @endforeach
    </ul>

    <a href="{{ route('pdf.macwin.index') }}" class="btn btn-primary mt-4">Upload Lagi</a>
</div>
