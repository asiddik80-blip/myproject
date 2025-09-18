<div class="container mt-5">
    <h3>Hasil Konversi</h3>
    <ul>
        @foreach ($fileNames as $name)
            <li>{{ $name }}</li>
        @endforeach
    </ul>
    <p>Total waktu proses: {{ $duration }} detik</p>
</div>

