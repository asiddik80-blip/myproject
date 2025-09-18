@extends('layouts.app-layer-dua')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">🧪 Hasil Deteksi Page D (OCR + Visual)</h3>


    {{-- 📌 Deteksi Anchor --}}
    <div class="mb-5">
        <h5 class="mb-3">📌 Deteksi Anchor ("ITEM-xxx")</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Text</th>
                    <th>X</th>
                    <th>Y</th>
                    <th>Width</th>
                    <th>Height</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($anchors as $i => $anchor)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><code>{{ $anchor['text'] }}</code></td>
                    <td>{{ $anchor['bounding_box']['x'] }}</td>
                    <td>{{ $anchor['bounding_box']['y'] }}</td>
                    <td>{{ $anchor['bounding_box']['width'] }}</td>
                    <td>{{ $anchor['bounding_box']['height'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 📐 Visual Boxes --}}
    <div class="mb-5">
        <h5 class="mb-3">📐 Visual Box (dari OpenCV)</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>X</th>
                    <th>Y</th>
                    <th>Width</th>
                    <th>Height</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($visual_boxes as $i => $box)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $box['x'] }}</td>
                    <td>{{ $box['y'] }}</td>
                    <td>{{ $box['width'] }}</td>
                    <td>{{ $box['height'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 📝 Caption Detector --}}
    <div class="mb-5">
        <h5 class="mb-3">📝 Caption Detected ("ITEM-xxx")</h5>
        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Text</th>
                    <th>Bounding Box</th>
                    <th>Confidence</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($captions as $i => $caption)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><code>{{ $caption['text'] }}</code></td>
                    <td>
                        x={{ $caption['bounding_box']['x'] }},
                        y={{ $caption['bounding_box']['y'] }},
                        w={{ $caption['bounding_box']['width'] }},
                        h={{ $caption['bounding_box']['height'] }}
                    </td>
                    <td>{{ $caption['ocr_confidence'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 📦 Tabel Placard Zone Pairing --}}
    <div class="mb-5">
        <h5 class="mb-3">📦 Placard Zone Pairing (VisualBox + Anchor)</h5>

        <table class="table table-bordered table-striped table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Anchor Text</th>
                    <th>Kode</th>
                    <th>Jenis</th>
                    <th>Visual Box</th>
                    <th>Text</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($placard_zones as $i => $pz)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $pz['anchorText'] }}</code></td>
                        <td> {{ $pz['kode-placard'] }}</td>
                        <td> {{ $pz['tipe-placard'] }}</td>
                        <td>
                            x={{ $pz['visualBox']['x'] }}, 
                            y={{ $pz['visualBox']['y'] }}, 
                            w={{ $pz['visualBox']['width'] }}, 
                            h={{ $pz['visualBox']['height'] }}
                        </td>
                        <td>
                            <pre class="mb-0" style="white-space: pre-wrap">{{ $pz['textBody'] ?? '-' }}</pre>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- 📄 JSON Debug Placard Zones --}}
    <div class="mt-4">
        <h5 class="mb-2 text-muted">🧾 Placard Zones (JSON Preview)</h5>
        <div class="bg-light border rounded p-3" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem;">
            <pre>{{ json_encode($placard_zones, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>

    <hr>
    <h4>Debug: TSV Result per Placard</h4>
    @foreach ($placard_zones as $index => $zone)
        <h5>ITEM: {{ $zone['anchorText'] }}</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Text</th>
                    <th>Conf</th>
                    <th>Top</th>
                    <th>Left</th>
                    <th>Width</th>
                    <th>Height</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($zone['tsvRaw'] ?? [] as $i => $entry)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $entry['text'] }}</td>
                        <td>{{ $entry['conf'] }}</td>
                        <td>{{ $entry['bbox']['top'] }}</td>
                        <td>{{ $entry['bbox']['left'] }}</td>
                        <td>{{ $entry['bbox']['width'] }}</td>
                        <td>{{ $entry['bbox']['height'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    <h5>Zona dari Gambar:</h5>
    <pre>{{ json_encode($zonesFromImage, JSON_PRETTY_PRINT) }}</pre>

    <h5>Detected Drawing-No Noise (Dibuang)</h5>
    <pre>@json($detectedNoiseWords, JSON_PRETTY_PRINT)</pre>




    {{-- 🖼️ Gambar hasil overlay --}}
    <div class="mt-5 text-center">
        <h5 class="mb-3">🖼️ Gambar dengan Overlay (JPEG)</h5>
        <img src="{{ asset('debug/overlay.jpg') }}" class="img-fluid border shadow" style="max-height: 800px;">
    </div>

    {{-- 📐 Gambar Zona Scan --}}
    <div class="mt-5 text-center">
        <h5 class="mb-3">📐 Zona Scan (Paper dan lainnya)</h5>
        <img src="{{ asset('debug/zone_overlay.jpg') }}" class="img-fluid border shadow" style="max-height: 800px;">
    </div>
</div>
@endsection
