@extends('layouts.app-layer-dua')

@section('content')
@php
    use Carbon\Carbon;

    $minutes = floor($duration / 60);
    $seconds = round($duration % 60, 2);
    $pdfCount = session('pdf_count', count($fileNames));
    $pageCounts = session('page_counts', []);
    $totalPages = array_sum($pageCounts);

    // Ambil metadata per file dari storage
    $metadatas = [];
    foreach ($fileNames as $name) {
        $metaPath = storage_path('app/uploads/' . $name . '_metadata.json');
        if (file_exists($metaPath)) {
            $json = json_decode(file_get_contents($metaPath), true);
            $metadatas[$name] = $json;
        }
    }

    // Placard Zones - data dari controller
    $allPlacardZones = $placardZones ?? [];

    // Group placardZones by file dan page
    $groupedPlacardZones = [];
    foreach ($allPlacardZones as $zone) {
        $file = $zone['file'] ?? 'unknown_file';
        $page = $zone['page'] ?? 'unknown_page';
        if (!isset($groupedPlacardZones[$file])) {
            $groupedPlacardZones[$file] = [];
        }
        if (!isset($groupedPlacardZones[$file][$page])) {
            $groupedPlacardZones[$file][$page] = [];
        }
        $groupedPlacardZones[$file][$page][] = $zone;
    }
@endphp

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary">{{ $minutes }} minute(s) {{ $seconds }} second(s)</h1>
        <h4>
            <span class="badge bg-info text-primary">
                PDF Convert result — total {{ $pdfCount }} files ({{ $totalPages }} pages)
            </span>
        </h4>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            {{-- Summary Table --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Convert Result</h4>
                    <p class="card-description">
                        Total : <code>{{ $pdfCount }} files ({{ $totalPages }} pages)</code>, click on file name below to see the details
                    </p>
                    

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>File Name</th>
                                    <th class="text-center">Pages</th>
                                    <th>Drawing No</th>
                                    <th class="text-center">Revisions</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fileNames as $index => $name)
                                    @php
                                        $meta = $metadatas[$name] ?? null;
                                        $drawingNo = $meta['drawing-no'] ?? '-';
                                        $revisionPages = $meta['page_type_distribution']['R'] ?? 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalDetailFile{{ $index }}">
                                                {{ $name }}
                                            </a>
                                        </td>
                                        <td class="text-center">{{ $pageCounts[$name] ?? '—' }}</td>
                                        <td><strong>{{ $drawingNo }}</strong></td>
                                        
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-primary p-0" data-bs-toggle="modal" data-bs-target="#ocrModal{{ $index }}">
                                                Detail
                                            </button>
                                        </td>
                                        <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">There is no file</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3 me-5 text-end">
                    <a href="{{ route('ocrpdf.downloadZip') }}" class="btn btn-outline-danger">
                        <i class="bi bi-file-zip me-1"></i>Download ZIP
                    </a>
                </div>
            </div>

            {{-- Placard Zones Summary --}}
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Placard Summary</h4>
                    @if(empty($groupedPlacardZones))
                        <p class="text-muted">No placard data available.</p>
                    @else
                        @foreach($groupedPlacardZones as $file => $pages)
                            <h5 class="text-danger">File: <strong>{{ $file }}.pdf</strong></h5>
                            @foreach($pages as $pageNum => $zones)
                                <h6>Page {{ $pageNum }}</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th>Caption Text</th>
                                                <th>Partlist</th>
                                                <th>Content</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($zones as $idx => $zone)
                                                <tr>
                                                    <td class="text-center">{{ $idx + 1 }}</td>
                                                    <td>{{ $zone['anchorText'] ?? '-' }}</td>
                                                    
                                                    <td>{{ $zone['partlist'] ?? '-' }}</td>
                                                    <td>{{ $zone['textBody'] ?? '-' }}</td>
                                                    
                                                
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Buttons --}}
            <div class="d-grid mt-4">
                <a href="{{ route('ocrpdf.index') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-upload me-2"></i>Upload New File
                </a>
            </div>
        </div>
    </div>
</div>




{{-- Modal OCR Detail --}}
@foreach ($fileNames as $index => $name)
    <div class="modal fade" id="ocrModal{{ $index }}" tabindex="-1" aria-labelledby="ocrModalLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ocrModalLabel{{ $index }}">File Name : {{ $name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        $base = pathinfo($name, PATHINFO_FILENAME);
                        $folder = storage_path('app/uploads');
                        $meta = $metadatas[$name] ?? null;
                        $pageTypes = $meta['page_type_detail'] ?? [];
                        $pageCount = $pageCounts[$name] ?? 0;
                    @endphp

                    @for ($i = 1; $i <= $pageCount; $i++)
                        @php
                            $pageNum = str_pad($i, 2, '0', STR_PAD_LEFT);
                            $jsonPath = $folder . "/{$base}_page_{$pageNum}.json";
                        @endphp

                        @if (file_exists($jsonPath))
                            @php
                                $json = json_decode(file_get_contents($jsonPath), true);
                                $lines = $json['lines'] ?? [];
                                $pageInfo = $pageTypes[$pageNum] ?? ['type' => '-', 'subtype' => null];
                                $type = $pageInfo['type'] ?? '-';
                                $subtype = $pageInfo['subtype'] ?? null;
                            @endphp

                            <div class="mb-4">
                                <h6 class="text-danger">PAGE {{ $i }} PDF</h6>
                                <p>
                                    <strong>PAGE TYPE {{ $type }}</strong>
                                    @if ($subtype)
                                        <span class="badge bg-secondary ms-2">Sub: {{ $subtype }}</span>
                                    @endif
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 80px;">No</th>
                                                <th>Text Content</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lines as $k => $line)
                                                <tr>
                                                    <td>{{ $k + 1 }}</td>
                                                    <td>{{ $line['text'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endfor
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Modal Detail Metadata per File --}}
@foreach ($fileNames as $index => $name)
    <div class="modal fade" id="modalDetailFile{{ $index }}" tabindex="-1" aria-labelledby="modalDetailFileLabel{{ $index }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailFileLabel{{ $index }}">{{ $name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @php
                        $meta = $metadatas[$name] ?? [];
                        $dist = $meta['page_type_distribution'] ?? [];
                        $detail = $meta['page_type_detail'] ?? [];
                    @endphp

                    <h6 class="fw-bold text-danger mb-2">File Information</h6>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Original Filename</th><td>{{ $meta['original_filename'] ?? '-' }}</td></tr>
                            <tr>
                                <th>Upload Time</th>
                                <td>
                                    @php
                                        $timestamp = $meta['upload_timestamp'] ?? null;
                                    @endphp
                                    {{ $timestamp ? \Carbon\Carbon::parse($timestamp)->format('d F Y | H:i') : '-' }}
                                </td>
                            </tr>
                            <tr><th>Total Pages</th><td>{{ $meta['total_pages'] ?? '-' }}</td></tr>
                            <tr><th>File Size</th><td>{{ $meta['pdf_filesize_kb'] ?? '-' }} KB</td></tr>
                            <tr><th>PDF Dimensions</th><td>{{ $meta['pdf_dimensions'] ?? '-' }}</td></tr>
                            <tr><th>Drawing No</th><td><strong>{{ $meta['drawing-no'] ?? '-' }}</strong></td></tr>
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-danger mt-4">Page Type Distribution</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr><th>Type</th><th>Total Pages</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($dist as $type => $count)
                                <tr><td>{{ $type }}</td><td>{{ $count }}</td></tr>
                            @empty
                                <tr><td colspan="2" class="text-muted">Tidak tersedia</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-danger mt-4">Page Type Detail</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr><th>Page</th><th>Type</th><th>Subtype</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($detail as $page => $info)
                                <tr>
                                    <td>{{ $page }}</td>
                                    <td>{{ $info['type'] ?? '-' }}</td>
                                    <td>{{ $info['subtype'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Nothing</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach



@endsection
