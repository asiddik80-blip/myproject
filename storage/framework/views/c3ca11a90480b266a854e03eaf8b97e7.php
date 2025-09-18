

<?php $__env->startSection('content'); ?>
<?php
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
?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary"><?php echo e($minutes); ?> minute(s) <?php echo e($seconds); ?> second(s)</h1>
        <h4>
            <span class="badge bg-info text-primary">
                PDF Convert result — total <?php echo e($pdfCount); ?> files (<?php echo e($totalPages); ?> pages)
            </span>
        </h4>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Convert Result</h4>
                    <p class="card-description">
                        Total : <code><?php echo e($pdfCount); ?> files (<?php echo e($totalPages); ?> pages)</code>, click on file name below to see the details
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
                                <?php $__empty_1 = true; $__currentLoopData = $fileNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php
                                        $meta = $metadatas[$name] ?? null;
                                        $drawingNo = $meta['drawing-no'] ?? '-';
                                        $revisionPages = $meta['page_type_distribution']['R'] ?? 0;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($index + 1); ?></td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalDetailFile<?php echo e($index); ?>">
                                                <?php echo e($name); ?>

                                            </a>
                                        </td>
                                        <td class="text-center"><?php echo e($pageCounts[$name] ?? '—'); ?></td>
                                        <td><strong><?php echo e($drawingNo); ?></strong></td>
                                        
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-primary p-0" data-bs-toggle="modal" data-bs-target="#ocrModal<?php echo e($index); ?>">
                                                Detail
                                            </button>
                                        </td>
                                        <td class="text-center"><i class="bi bi-check-circle-fill text-success"></i></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">There is no file</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3 me-5 text-end">
                    <a href="<?php echo e(route('ocrpdf.downloadZip')); ?>" class="btn btn-outline-danger">
                        <i class="bi bi-file-zip me-1"></i>Download ZIP
                    </a>
                </div>
            </div>

            
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Placard Summary</h4>
                    <?php if(empty($groupedPlacardZones)): ?>
                        <p class="text-muted">No placard data available.</p>
                    <?php else: ?>
                        <?php $__currentLoopData = $groupedPlacardZones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file => $pages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <h5 class="text-danger">File: <strong><?php echo e($file); ?>.pdf</strong></h5>
                            <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pageNum => $zones): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <h6>Page <?php echo e($pageNum); ?></h6>
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
                                            <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td class="text-center"><?php echo e($idx + 1); ?></td>
                                                    <td><?php echo e($zone['anchorText'] ?? '-'); ?></td>
                                                    
                                                    <td><?php echo e($zone['partlist'] ?? '-'); ?></td>
                                                    <td><?php echo e($zone['textBody'] ?? '-'); ?></td>
                                                    
                                                
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="d-grid mt-4">
                <a href="<?php echo e(route('ocrpdf.index')); ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-upload me-2"></i>Upload New File
                </a>
            </div>
        </div>
    </div>
</div>





<?php $__currentLoopData = $fileNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="ocrModal<?php echo e($index); ?>" tabindex="-1" aria-labelledby="ocrModalLabel<?php echo e($index); ?>" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ocrModalLabel<?php echo e($index); ?>">File Name : <?php echo e($name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                        $base = pathinfo($name, PATHINFO_FILENAME);
                        $folder = storage_path('app/uploads');
                        $meta = $metadatas[$name] ?? null;
                        $pageTypes = $meta['page_type_detail'] ?? [];
                        $pageCount = $pageCounts[$name] ?? 0;
                    ?>

                    <?php for($i = 1; $i <= $pageCount; $i++): ?>
                        <?php
                            $pageNum = str_pad($i, 2, '0', STR_PAD_LEFT);
                            $jsonPath = $folder . "/{$base}_page_{$pageNum}.json";
                        ?>

                        <?php if(file_exists($jsonPath)): ?>
                            <?php
                                $json = json_decode(file_get_contents($jsonPath), true);
                                $lines = $json['lines'] ?? [];
                                $pageInfo = $pageTypes[$pageNum] ?? ['type' => '-', 'subtype' => null];
                                $type = $pageInfo['type'] ?? '-';
                                $subtype = $pageInfo['subtype'] ?? null;
                            ?>

                            <div class="mb-4">
                                <h6 class="text-danger">PAGE <?php echo e($i); ?> PDF</h6>
                                <p>
                                    <strong>PAGE TYPE <?php echo e($type); ?></strong>
                                    <?php if($subtype): ?>
                                        <span class="badge bg-secondary ms-2">Sub: <?php echo e($subtype); ?></span>
                                    <?php endif; ?>
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
                                            <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($k + 1); ?></td>
                                                    <td><?php echo e($line['text']); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<?php $__currentLoopData = $fileNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="modalDetailFile<?php echo e($index); ?>" tabindex="-1" aria-labelledby="modalDetailFileLabel<?php echo e($index); ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailFileLabel<?php echo e($index); ?>"><?php echo e($name); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                        $meta = $metadatas[$name] ?? [];
                        $dist = $meta['page_type_distribution'] ?? [];
                        $detail = $meta['page_type_detail'] ?? [];
                    ?>

                    <h6 class="fw-bold text-danger mb-2">File Information</h6>
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th>Original Filename</th><td><?php echo e($meta['original_filename'] ?? '-'); ?></td></tr>
                            <tr>
                                <th>Upload Time</th>
                                <td>
                                    <?php
                                        $timestamp = $meta['upload_timestamp'] ?? null;
                                    ?>
                                    <?php echo e($timestamp ? \Carbon\Carbon::parse($timestamp)->format('d F Y | H:i') : '-'); ?>

                                </td>
                            </tr>
                            <tr><th>Total Pages</th><td><?php echo e($meta['total_pages'] ?? '-'); ?></td></tr>
                            <tr><th>File Size</th><td><?php echo e($meta['pdf_filesize_kb'] ?? '-'); ?> KB</td></tr>
                            <tr><th>PDF Dimensions</th><td><?php echo e($meta['pdf_dimensions'] ?? '-'); ?></td></tr>
                            <tr><th>Drawing No</th><td><strong><?php echo e($meta['drawing-no'] ?? '-'); ?></strong></td></tr>
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-danger mt-4">Page Type Distribution</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr><th>Type</th><th>Total Pages</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $dist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr><td><?php echo e($type); ?></td><td><?php echo e($count); ?></td></tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="2" class="text-muted">Tidak tersedia</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <h6 class="fw-bold text-danger mt-4">Page Type Detail</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr><th>Page</th><th>Type</th><th>Subtype</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $detail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($page); ?></td>
                                    <td><?php echo e($info['type'] ?? '-'); ?></td>
                                    <td><?php echo e($info['subtype'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="3" class="text-muted">Nothing</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app-layer-dua', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\myproject\resources\views/ocrpdf/result.blade.php ENDPATH**/ ?>